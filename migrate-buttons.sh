#!/bin/bash
set -euo pipefail

#===============================================================================
# Button Migration Orchestrator
# Automates migration of legacy buttons to twig:ibexa:button component
#===============================================================================

# Configuration
readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly WORK_DIR="$SCRIPT_DIR/.button-migration"
readonly PLAN_FILE="$SCRIPT_DIR/BUTTON_MIGRATION_PLAN.txt"
readonly PROMPT_TEMPLATE="$WORK_DIR/prompts/migrate-chunk.txt"
readonly LOG_FILE="$WORK_DIR/migration.log"
readonly FAILED_FILE="$WORK_DIR/failed-chunks.txt"
readonly PROGRESS_FILE="$WORK_DIR/progress.txt"

# Settings
readonly MAX_RETRIES=2  # 3 total attempts (initial + 2 retries)
readonly TEST_TIMEOUT=300  # 5 minutes
readonly OPENCODE_MODEL="anthropic/claude-sonnet-4-5-20250929"

# Colors
readonly GREEN='\033[0;32m'
readonly RED='\033[0;31m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly CYAN='\033[0;36m'
readonly NC='\033[0m'  # No Color

#===============================================================================
# LOGGING
#===============================================================================

log() {
    local level=$1
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] [$level] $message" >> "$LOG_FILE"
    
    # Also log errors to stderr
    if [[ "$level" == "ERROR" ]]; then
        echo "[$timestamp] [$level] $message" >&2
    fi
}

log_and_echo() {
    local level=$1
    shift
    local message="$*"
    log "$level" "$message"
    echo -e "$message"
}

#===============================================================================
# VALIDATION
#===============================================================================

validate_environment() {
    log "INFO" "Validating environment..."
    
    # Check OpenCode
    if ! command -v opencode &> /dev/null; then
        log_and_echo "ERROR" "${RED}✗ opencode not found${NC}"
        echo "  Install: npm install -g @opencode/cli"
        exit 1
    fi
    log "INFO" "✓ OpenCode found: $(which opencode)"
    
    # Check Yarn
    if ! command -v yarn &> /dev/null; then
        log_and_echo "ERROR" "${RED}✗ yarn not found${NC}"
        echo "  Install Node.js and Yarn first"
        exit 1
    fi
    log "INFO" "✓ Yarn found: $(which yarn)"
    
    # Check Git
    if ! git rev-parse --git-dir &> /dev/null; then
        log_and_echo "ERROR" "${RED}✗ Not in a git repository${NC}"
        exit 1
    fi
    log "INFO" "✓ Git repository detected"
    
    # Check for uncommitted changes
    if ! git diff-index --quiet HEAD -- 2>/dev/null; then
        log_and_echo "ERROR" "${RED}✗ Uncommitted changes detected${NC}"
        echo "  Commit or stash your changes first: git status"
        exit 1
    fi
    log "INFO" "✓ No uncommitted changes"
    
    # Check branch (not main/master)
    local branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    if [[ "$branch" == "main" || "$branch" == "master" ]]; then
        log_and_echo "ERROR" "${RED}✗ Cannot run on main/master branch${NC}"
        echo "  Create a feature branch first: git checkout -b feature/button-migration"
        exit 1
    fi
    log "INFO" "✓ On feature branch: $branch"
    
    # Check required files
    if [[ ! -f "$PLAN_FILE" ]]; then
        log_and_echo "ERROR" "${RED}✗ Migration plan not found: $PLAN_FILE${NC}"
        exit 1
    fi
    log "INFO" "✓ Migration plan found"
    
    if [[ ! -f "MIGRATION_RULES.md" ]]; then
        log_and_echo "ERROR" "${RED}✗ MIGRATION_RULES.md not found${NC}"
        exit 1
    fi
    log "INFO" "✓ MIGRATION_RULES.md found"
    
    if [[ ! -f ".claude/skills/validate-frontend-changes.md" ]]; then
        log_and_echo "ERROR" "${RED}✗ validate-frontend-changes skill not found${NC}"
        exit 1
    fi
    log "INFO" "✓ validate-frontend-changes skill found"
    
    log_and_echo "SUCCESS" "${GREEN}✓ Environment validation passed${NC}"
}

#===============================================================================
# JS FILE DETECTION
#===============================================================================

identify_related_js() {
    local twig_file=$1
    local js_dir="src/bundle/Resources/public/js/scripts"
    
    # Extract patterns from file path
    local filename=$(basename "$twig_file" .html.twig)
    local category=$(dirname "$twig_file" | cut -d'/' -f1)
    
    # Strategy 1: Look for JS files that reference button classes
    local found_js=$(find "$js_dir" -name "*.js" -type f 2>/dev/null | while read js_file; do
        # Check if this JS file references ibexa-btn classes
        if grep -q "ibexa-btn" "$js_file" 2>/dev/null; then
            # Check if it might be related to this template
            # Match by filename keywords or category
            local js_basename=$(basename "$js_file" .js)
            
            # Direct match or keyword match
            if echo "$js_basename" | grep -q "$category"; then
                echo "$js_file"
            elif echo "$filename" | grep -E "$(echo $js_basename | sed 's/admin\.//' | sed 's/\./-/g')" &>/dev/null; then
                echo "$js_file"
            fi
        fi
    done | sort -u)
    
    echo "$found_js"
}

#===============================================================================
# PROMPT BUILDING
#===============================================================================

build_opencode_prompt() {
    local chunk_id=$1
    shift
    local twig_files=("$@")
    
    # Get category from first file
    local category=$(dirname "${twig_files[0]}" | cut -d'/' -f1)
    
    # Count files
    local twig_count=${#twig_files[@]}
    
    # Build Twig file list with numbering
    local twig_list=""
    for i in "${!twig_files[@]}"; do
        twig_list+="  $((i+1)). ${twig_files[$i]}"$'\n'
    done
    
    # Identify related JS files
    local js_files=()
    for twig in "${twig_files[@]}"; do
        local related_js=$(identify_related_js "$twig")
        if [[ -n "$related_js" ]]; then
            while IFS= read -r js_file; do
                js_files+=("$js_file")
            done <<< "$related_js"
        fi
    done
    
    # Remove duplicates (handle empty array)
    local unique_js=()
    if [[ ${#js_files[@]} -gt 0 ]]; then
        unique_js=($(printf '%s\n' "${js_files[@]}" | sort -u))
    fi
    local js_count=${#unique_js[@]}
    
    # Build JS file list
    local js_list=""
    if [[ $js_count -gt 0 ]]; then
        for i in "${!unique_js[@]}"; do
            local js_basename=$(basename "${unique_js[$i]}")
            js_list+="  $((i+1)). $js_basename"$'\n'
        done
    else
        js_list="  (None detected - pure template migration)"$'\n'
    fi
    
    # Check for unmapped variants
    local special_notes=""
    local has_unmapped=$(grep "^${chunk_id}|" "$PLAN_FILE" | grep -c "|1|" || echo "0")
    
    if [[ "$has_unmapped" != "0" ]]; then
        local unmapped_info=$(grep "^${chunk_id}|" "$PLAN_FILE" | grep "|1|" | head -1 | cut -d'|' -f6)
        unmapped_info="${unmapped_info#UNMAPPED:}"
        special_notes="

⚠️ SPECIAL HANDLING REQUIRED:
This chunk contains UNMAPPED button variants: $unmapped_info
These variants are NOT yet supported by twig:ibexa:button component.

For buttons with unmapped variants, ADD TODO COMMENT:
{# TODO: Migrate to twig:ibexa:button component
   Reason: Uses unmapped variant '$unmapped_info' not in design system
   See: MIGRATION_RULES.md Section 1 - Additional Legacy Variants (Not Yet Mapped)
#}

Keep these buttons as legacy <button> markup for now."
    fi
    
    # Load template and substitute variables
    if [[ ! -f "$PROMPT_TEMPLATE" ]]; then
        log "ERROR" "Prompt template not found: $PROMPT_TEMPLATE"
        return 1
    fi
    
    local prompt=$(cat "$PROMPT_TEMPLATE")
    prompt="${prompt//\{\{CHUNK_ID\}\}/$chunk_id}"
    prompt="${prompt//\{\{CATEGORY\}\}/$category}"
    prompt="${prompt//\{\{FILE_COUNT\}\}/$twig_count}"
    prompt="${prompt//\{\{JS_COUNT\}\}/$js_count}"
    prompt="${prompt//\{\{TWIG_FILE_LIST\}\}/$twig_list}"
    prompt="${prompt//\{\{JS_FILE_LIST\}\}/$js_list}"
    prompt="${prompt//\{\{SPECIAL_NOTES\}\}/$special_notes}"
    
    echo "$prompt"
}

#===============================================================================
# OPENCODE EXECUTION
#===============================================================================

run_opencode_migration() {
    local chunk_id=$1
    shift
    local twig_files=("$@")
    local attempt=${ATTEMPT:-1}
    
    log "INFO" "[$chunk_id] Running OpenCode migration (attempt $attempt)"
    
    # Build file arguments
    local file_args=()
    
    # Add Twig files
    for twig in "${twig_files[@]}"; do
        local full_path="src/bundle/Resources/views/themes/admin/$twig"
        if [[ -f "$full_path" ]]; then
            file_args+=(-f "$full_path")
        else
            log "WARN" "[$chunk_id] File not found: $full_path"
        fi
    done
    
    # Add related JS files
    local js_files=()
    for twig in "${twig_files[@]}"; do
        local related_js=$(identify_related_js "$twig")
        if [[ -n "$related_js" ]]; then
            while IFS= read -r js_file; do
                if [[ -f "$js_file" ]]; then
                    file_args+=(-f "$js_file")
                    js_files+=("$js_file")
                fi
            done <<< "$related_js"
        fi
    done
    
    # Remove duplicate JS files (handle empty array)
    local unique_js=()
    if [[ ${#js_files[@]} -gt 0 ]]; then
        unique_js=($(printf '%s\n' "${js_files[@]}" | sort -u))
    fi
    
    # Add reference files
    file_args+=(-f "MIGRATION_RULES.md")
    file_args+=(-f ".claude/skills/validate-frontend-changes.md")
    
    log "INFO" "[$chunk_id] Files: ${#twig_files[@]} Twig, ${#unique_js[@]} JS, 2 docs"
    
    # Build prompt and save to file (avoids ENAMETOOLONG error)
    local prompt=$(build_opencode_prompt "$chunk_id" "${twig_files[@]}")
    if [[ -z "$prompt" ]]; then
        log "ERROR" "[$chunk_id] Failed to build prompt"
        return 1
    fi
    
    local prompt_file="$WORK_DIR/prompt-${chunk_id}-attempt${attempt}.txt"
    echo "$prompt" > "$prompt_file"
    
    # Add prompt file to file list
    file_args+=(-f "$prompt_file")
    
    # Run OpenCode with NEW session (avoid context blow-up)
    echo ""
    echo -e "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m"
    echo -e "\033[1;36m  OpenCode Migration: $chunk_id\033[0m"
    echo -e "\033[1;36m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m"
    log "INFO" "[$chunk_id] Calling OpenCode with model $OPENCODE_MODEL..."
    log "INFO" "[$chunk_id] Twig files: ${#twig_files[@]}, JS files: ${#unique_js[@]}, Docs: 2"
    
    local opencode_log="$WORK_DIR/opencode-${chunk_id}-attempt${attempt}.log"
    local message="Execute the button migration according to the instructions in the attached prompt file. Follow all rules from MIGRATION_RULES.md. DO NOT run any bash commands - just edit the files."
    log "INFO" "[$chunk_id] Message: $message"
    echo ""
    echo -e "\033[0;33m⏳ OpenCode is working (this may take 2-5 minutes)...\033[0m"
    echo -e "\033[0;90mWatch progress: tail -f .button-migration/opencode-${chunk_id}-attempt${attempt}.log\033[0m"
    echo ""
    
    local exit_code=0
    
    # IMPORTANT: Message MUST come BEFORE -f flags in opencode run command
    # Use 'tee' to show output in real-time AND log it
    opencode run \
        -m "$OPENCODE_MODEL" \
        --title "Button Migration: $chunk_id" \
        "$message" \
        "${file_args[@]}" 2>&1 | tee "$opencode_log"
    exit_code=${PIPESTATUS[0]}
    
    if [[ $exit_code -eq 0 ]]; then
        log "SUCCESS" "[$chunk_id] OpenCode completed successfully"
        return 0
    else
        log "ERROR" "[$chunk_id] OpenCode failed with exit code $exit_code"
        log "ERROR" "[$chunk_id] See log: $opencode_log"
        return $exit_code
    fi
}

#===============================================================================
# VALIDATION
#===============================================================================

validate_changes() {
    local chunk_id=$1
    
    echo ""
    echo -e "\033[1;35m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m"
    echo -e "\033[1;35m  Running Validation: yarn test\033[0m"
    echo -e "\033[1;35m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\033[0m"
    log "INFO" "[$chunk_id] Running frontend validation (yarn test)..."
    echo -e "\033[0;33m⏳ This will check: Prettier, TypeScript, ESLint (timeout: ${TEST_TIMEOUT}s)...\033[0m"
    echo ""
    
    local test_output="$WORK_DIR/test-output-${chunk_id}.log"
    local exit_code=0
    
    # Run tests with timeout (macOS compatible using background process + kill)
    yarn test 2>&1 | tee "$test_output" &
    local test_pid=$!
    
    # Wait for process with timeout
    local count=0
    while kill -0 $test_pid 2>/dev/null; do
        if [[ $count -ge $TEST_TIMEOUT ]]; then
            kill $test_pid 2>/dev/null
            wait $test_pid 2>/dev/null
            exit_code=124  # Timeout exit code
            break
        fi
        sleep 1
        ((count++))
    done
    
    # Get actual exit code if not timed out
    if [[ $exit_code -eq 0 ]]; then
        wait $test_pid 2>/dev/null
        exit_code=$?
    fi
    
    if [[ $exit_code -eq 0 ]]; then
        log "SUCCESS" "[$chunk_id] Tests PASSED ✓"
        return 0
    elif [[ $exit_code -eq 124 ]]; then
        log "ERROR" "[$chunk_id] Tests TIMED OUT (${TEST_TIMEOUT}s)"
        return 1
    else
        log "ERROR" "[$chunk_id] Tests FAILED (exit code: $exit_code)"
        
        # Extract key errors for logging
        if [[ -f "$test_output" ]]; then
            local errors=$(grep -E "ERROR|FAIL|✗|×" "$test_output" 2>/dev/null | head -5 || echo "No specific errors found")
            while IFS= read -r line; do
                log "ERROR" "[$chunk_id]   $line"
            done <<< "$errors"
        fi
        
        return 1
    fi
}

#===============================================================================
# RETRY LOGIC
#===============================================================================

handle_chunk_with_retry() {
    local chunk_id=$1
    shift
    local twig_files=("$@")
    
    local attempt=1
    local max_attempts=$((MAX_RETRIES + 1))
    
    log "INFO" "[$chunk_id] Starting processing (max $max_attempts attempts)"
    
    while [[ $attempt -le $max_attempts ]]; do
        log "INFO" "[$chunk_id] Attempt $attempt/$max_attempts"
        
        # Run migration
        ATTEMPT=$attempt run_opencode_migration "$chunk_id" "${twig_files[@]}"
        local migration_result=$?
        
        if [[ $migration_result -ne 0 ]]; then
            log "ERROR" "[$chunk_id] Migration failed (attempt $attempt)"
            
            if [[ $attempt -lt $max_attempts ]]; then
                log "WARN" "[$chunk_id] Reverting and retrying..."
                git restore . 2>/dev/null
                git clean -fd 2>/dev/null
                attempt=$((attempt + 1))
                sleep 2
                continue
            else
                log "ERROR" "[$chunk_id] Max attempts reached - SKIPPING"
                echo "$chunk_id" >> "$FAILED_FILE"
                git restore . 2>/dev/null
                git clean -fd 2>/dev/null
                return 1
            fi
        fi
        
        # Validate changes
        if validate_changes "$chunk_id"; then
            log "SUCCESS" "[$chunk_id] Validation passed - chunk complete"
            return 0
        else
            log "ERROR" "[$chunk_id] Validation failed (attempt $attempt)"
            
            if [[ $attempt -lt $max_attempts ]]; then
                log "WARN" "[$chunk_id] Reverting and retrying with error context..."
                git restore . 2>/dev/null
                git clean -fd 2>/dev/null
                attempt=$((attempt + 1))
                sleep 2
                continue
            else
                log "ERROR" "[$chunk_id] Max attempts reached - SKIPPING"
                echo "$chunk_id" >> "$FAILED_FILE"
                git restore . 2>/dev/null
                git clean -fd 2>/dev/null
                return 1
            fi
        fi
    done
}

#===============================================================================
# GIT OPERATIONS
#===============================================================================

create_commit() {
    local chunk_id=$1
    local category=$2
    shift 2
    local files=("$@")
    
    log "INFO" "[$chunk_id] Creating git commit..."
    
    # Check if there are changes
    if git diff-index --quiet HEAD --; then
        log "WARN" "[$chunk_id] No changes to commit"
        return 0
    fi
    
    # Stage all changes
    git add -A
    
    # Build file list for commit message
    local file_list=""
    for file in "${files[@]}"; do
        file_list+="- $file"$'\n'
    done
    
    # Create commit message
    local commit_msg="Migrate buttons: ${chunk_id} - ${category}

Files migrated (${#files[@]}):
${file_list}
- Migrated legacy <button> to twig:ibexa:button component
- Mapped variants: ghost→tertiary, primary, secondary, small size
- Preserved data attributes, custom classes, and functionality
- Updated JavaScript selectors where needed
- Tests: PASSED

See: MIGRATION_RULES.md"
    
    # Create commit
    if git commit -m "$commit_msg" >> "$LOG_FILE" 2>&1; then
        local commit_sha=$(git rev-parse --short HEAD)
        log "SUCCESS" "[$chunk_id] Committed: $commit_sha"
        return 0
    else
        log "ERROR" "[$chunk_id] Failed to create commit"
        return 1
    fi
}

#===============================================================================
# PROGRESS DISPLAY
#===============================================================================

show_progress() {
    local current=$1
    local total=$2
    local chunk_id=$3
    local status=$4  # SUCCESS, FAILED, RUNNING
    
    local percent=$((current * 100 / total))
    local filled=$((percent * 40 / 100))
    local empty=$((40 - filled))
    
    case $status in
        SUCCESS) 
            local color=$GREEN
            local symbol="✓"
            ;;
        FAILED)  
            local color=$RED
            local symbol="✗"
            ;;
        *)
            local color=$YELLOW
            local symbol="→"
            ;;
    esac
    
    printf "\r${color}["
    printf "%0.s█" $(seq 1 $filled)
    printf "%0.s░" $(seq 1 $empty)
    printf "] %s %3d/%d chunks (%3d%%)${NC} ${CYAN}%s${NC}" \
        "$symbol" "$current" "$total" "$percent" "$chunk_id"
    
    if [[ "$status" != "RUNNING" ]]; then
        echo  # New line
    fi
}

#===============================================================================
# MAIN ORCHESTRATION
#===============================================================================

parse_options() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --dry-run)
                DRY_RUN=true
                shift
                ;;
            --chunk)
                SINGLE_CHUNK="$2"
                shift 2
                ;;
            --start-from)
                START_FROM="$2"
                shift 2
                ;;
            --help)
                show_help
                exit 0
                ;;
            *)
                echo "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done
}

show_help() {
    cat << EOF
Button Migration Orchestrator

Usage: $0 [OPTIONS]

Options:
  --dry-run          Show what would be done without making changes
  --chunk CHUNK_ID   Process only specific chunk (e.g., CHUNK_05)
  --start-from N     Start from chunk N and continue to end
  --help             Show this help message

Examples:
  $0                          # Run full migration
  $0 --dry-run                # Preview without changes
  $0 --chunk CHUNK_05         # Process only chunk 5
  $0 --start-from 10          # Resume from chunk 10

See ORCHESTRATION_README.md for complete documentation.
EOF
}

main() {
    # Parse command line options
    DRY_RUN=false
    SINGLE_CHUNK=""
    START_FROM=0
    parse_options "$@"
    
    # Header
    echo -e "${BLUE}═══════════════════════════════════════${NC}"
    echo -e "${BLUE}  Button Migration Orchestrator${NC}"
    echo -e "${BLUE}═══════════════════════════════════════${NC}"
    echo
    
    # Setup working directory
    mkdir -p "$WORK_DIR/prompts"
    mkdir -p "$WORK_DIR/logs"
    
    # Initialize log
    echo "=== Button Migration Started: $(date) ===" > "$LOG_FILE"
    log "INFO" "Working directory: $WORK_DIR"
    log "INFO" "Plan file: $PLAN_FILE"
    log "INFO" "OpenCode model: $OPENCODE_MODEL"
    
    # Validate environment
    validate_environment
    echo
    
    # Parse migration plan (bash 3.2 compatible - no associative arrays)
    log "INFO" "Parsing migration plan..."
    
    # First pass: collect unique chunks
    local chunks=()
    local seen_chunks=""
    while IFS='|' read -r chunk_id file_path button_count unmapped category notes; do
        # Skip comments and empty lines
        [[ "$chunk_id" =~ ^#.*$ || -z "$chunk_id" ]] && continue
        
        # Add chunk if not seen before
        if [[ ! "$seen_chunks" =~ $chunk_id ]]; then
            chunks+=("$chunk_id")
            seen_chunks="$seen_chunks $chunk_id"
        fi
    done < "$PLAN_FILE"
    
    local total_chunks=${#chunks[@]}
    log "INFO" "Loaded $total_chunks chunks from plan"
    echo -e "${CYAN}Total chunks: $total_chunks${NC}"
    echo
    
    if [[ "$DRY_RUN" == true ]]; then
        echo -e "${YELLOW}DRY RUN MODE - No changes will be made${NC}"
        echo
        for idx in "${!chunks[@]}"; do
            local chunk_id="${chunks[$idx]}"
            # Count files for this chunk
            local file_count=$(grep "^${chunk_id}|" "$PLAN_FILE" | wc -l | tr -d ' ')
            echo "[$((idx+1))/$total_chunks] $chunk_id: $file_count files"
        done
        echo
        echo "Run without --dry-run to execute migration"
        exit 0
    fi
    
    # Clear failed chunks file
    > "$FAILED_FILE"
    
    # Process chunks
    local success_count=0
    local failed_count=0
    local start_idx=0
    
    # Handle --start-from
    if [[ $START_FROM -gt 0 ]]; then
        start_idx=$((START_FROM - 1))
        echo -e "${YELLOW}Starting from chunk $START_FROM${NC}"
        echo
    fi
    
    # Helper function to get files for a chunk
    get_chunk_files() {
        local chunk_id=$1
        grep "^${chunk_id}|" "$PLAN_FILE" | cut -d'|' -f2
    }
    
    # Handle --chunk (single chunk mode)
    if [[ -n "$SINGLE_CHUNK" ]]; then
        echo -e "${YELLOW}Processing single chunk: $SINGLE_CHUNK${NC}"
        echo
        
        # Get files for this chunk
        local files_str=$(get_chunk_files "$SINGLE_CHUNK" | tr '\n' ' ')
        if [[ -z "$files_str" ]]; then
            echo -e "${RED}Chunk not found: $SINGLE_CHUNK${NC}"
            exit 1
        fi
        
        local files_array=($files_str)
        local category=$(dirname "${files_array[0]}" | cut -d'/' -f1)
        
        if handle_chunk_with_retry "$SINGLE_CHUNK" "${files_array[@]}"; then
            create_commit "$SINGLE_CHUNK" "$category" "${files_array[@]}"
            echo -e "${GREEN}✓ $SINGLE_CHUNK completed successfully${NC}"
            exit 0
        else
            echo -e "${RED}✗ $SINGLE_CHUNK failed${NC}"
            exit 1
        fi
    fi
    
    # Process all chunks
    for idx in $(seq $start_idx $((total_chunks - 1))); do
        local chunk_id="${chunks[$idx]}"
        local chunk_num=$((idx + 1))
        
        # Get files for this chunk
        local files_str=$(get_chunk_files "$chunk_id" | tr '\n' ' ')
        local files_array=($files_str)
        local category=$(dirname "${files_array[0]}" | cut -d'/' -f1)
        
        show_progress "$chunk_num" "$total_chunks" "$chunk_id" "RUNNING"
        
        # Process chunk with retry logic
        if handle_chunk_with_retry "$chunk_id" "${files_array[@]}"; then
            create_commit "$chunk_id" "$category" "${files_array[@]}"
            show_progress "$chunk_num" "$total_chunks" "$chunk_id" "SUCCESS"
            success_count=$((success_count + 1))
        else
            show_progress "$chunk_num" "$total_chunks" "$chunk_id" "FAILED"
            failed_count=$((failed_count + 1))
        fi
        
        # Save progress
        echo "$chunk_num" > "$PROGRESS_FILE"
    done
    
    # Final report
    echo
    echo -e "${BLUE}═══════════════════════════════════════${NC}"
    echo -e "${GREEN}  Migration Complete!${NC}"
    echo -e "${BLUE}═══════════════════════════════════════${NC}"
    echo
    echo -e "✓ Successful: ${GREEN}$success_count${NC} chunks"
    
    if [[ $failed_count -gt 0 ]]; then
        echo -e "✗ Failed: ${RED}$failed_count${NC} chunks"
        echo
        echo -e "${YELLOW}Failed chunks:${NC}"
        cat "$FAILED_FILE" | while read chunk; do
            echo "  - $chunk"
        done
    fi
    
    echo
    echo -e "${CYAN}📋 Log:${NC} $LOG_FILE"
    echo -e "${CYAN}🔍 Review commits:${NC} git log --oneline -$success_count"
    echo
    echo -e "${YELLOW}Next steps:${NC}"
    echo "1. Review failed chunks (if any) and migrate manually"
    echo "2. Run full test suite: ${CYAN}yarn test${NC}"
    echo "3. Test in browser manually"
    echo "4. Build assets from project root: ${CYAN}yarn ibexa:dev${NC}"
    echo "5. When ready, push changes: ${CYAN}git push origin $(git rev-parse --abbrev-ref HEAD)${NC}"
    echo
    
    log "INFO" "=== Migration completed: $success_count success, $failed_count failed ==="
}

# Run main function
main "$@"

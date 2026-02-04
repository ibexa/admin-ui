/**
 * Build OpenCode prompts from templates
 */

import { readFileSync } from 'fs';
import { Chunk } from '../types.js';

export function buildPrompt(chunk: Chunk, templateFile: string): string {
  const template = readFileSync(templateFile, 'utf-8');
  
  // Build file list with numbering
  const fileList = chunk.files
    .map((file, i) => `  ${i + 1}. ${file}`)
    .join('\n');
  
  // Check for unmapped variants
  let specialNotes = '';
  if (chunk.hasUnmapped) {
    specialNotes = `

⚠️ SPECIAL HANDLING REQUIRED:
This chunk contains UNMAPPED button variants.
These variants are NOT yet supported by twig:ibexa:button component.

For buttons with unmapped variants, ADD TODO COMMENT:
{# TODO: Migrate to twig:ibexa:button component
   Reason: Uses unmapped variant not in design system
   See: MIGRATION_RULES.md Section 1 - Additional Legacy Variants (Not Yet Mapped)
#}

Keep these buttons as legacy <button> markup for now.`;
  }
  
  // Substitute variables
  let prompt = template;
  prompt = prompt.replace(/\{\{CHUNK_ID\}\}/g, chunk.id);
  prompt = prompt.replace(/\{\{CATEGORY\}\}/g, chunk.category);
  prompt = prompt.replace(/\{\{FILE_COUNT\}\}/g, chunk.files.length.toString());
  prompt = prompt.replace(/\{\{TWIG_FILE_LIST\}\}/g, fileList);
  prompt = prompt.replace(/\{\{SPECIAL_NOTES\}\}/g, specialNotes);
  
  return prompt;
}

/**
 * Parse migration plan files
 */

import { readFileSync } from 'fs';
import { Chunk } from '../types.js';
import { logger } from './logger.js';

export function parseChunks(planFile: string): Chunk[] {
  const content = readFileSync(planFile, 'utf-8');
  const lines = content.split('\n');
  
  const chunkMap = new Map<string, Chunk>();
  
  for (const line of lines) {
    // Skip comments and empty lines
    if (line.startsWith('#') || line.trim() === '') continue;
    
    // Parse format: CHUNK_ID|FILE_PATH|BUTTON_COUNT|HAS_UNMAPPED|CATEGORY|NOTES
    const parts = line.split('|');
    if (parts.length < 5) continue;
    
    const [chunkId, filePath, buttonCount, hasUnmapped, category, notes] = parts;
    
    if (!chunkMap.has(chunkId)) {
      chunkMap.set(chunkId, {
        id: chunkId,
        files: [],
        category: category.trim(),
        buttonCount: 0,
        hasUnmapped: hasUnmapped === '1',
        notes: notes?.trim()
      });
    }
    
    const chunk = chunkMap.get(chunkId)!;
    chunk.files.push(filePath.trim());
    chunk.buttonCount += parseInt(buttonCount) || 0;
  }
  
  const chunks = Array.from(chunkMap.values());
  logger.debug(`Parsed ${chunks.length} chunks from ${planFile}`);
  
  return chunks;
}

export function getChunkById(chunks: Chunk[], id: string): Chunk | undefined {
  return chunks.find(c => c.id === id);
}

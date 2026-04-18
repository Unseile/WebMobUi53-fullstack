/**
 * Normalize text by removing diacritics (accents) and converting to lowercase
 * @param {string} text - The text to normalize
 * @returns {string} - Normalized text without diacritics
 */
export function normalizeForSearch(text) {
  if (!text) return '';

  return text
    .toLowerCase()
    .normalize('NFKD') // Decompose characters with diacritics
    .replace(/\p{Mn}/gu, ''); // Remove diacritical marks
}

export function equals(str1, str2) {
  return normalizeForSearch(str1) === normalizeForSearch(str2);
}
/**
 * Check if text contains search term, ignoring diacritics
 * @param {string} text - The text to search in
 * @param {string} searchTerm - The search term
 * @returns {boolean} - True if text contains search term
 */
export function includes(text, searchTerm) {
  return normalizeForSearch(text).includes(normalizeForSearch(searchTerm));
}

const normalizeText = value => String(value ?? '')
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .toLowerCase();

const normalize = value => normalizeText(value)
  .replace(/[^a-z0-9]+/g, '-')
  .replace(/^-+|-+$/g, '');

const tokens = value => new Set(normalize(value).split('-').filter(Boolean));

const similarity = (left, right) => {
  const leftTokens = tokens(left);
  const rightTokens = tokens(right);
  const intersection = [...leftTokens].filter(token => rightTokens.has(token)).length;
  const union = new Set([...leftTokens, ...rightTokens]).size;
  return union ? intersection / union : 0;
};

export function validateTopic(topic, policy) {
  const text = normalizeText(topic);
  const blockedTerms = (policy?.blockedTerms ?? []).map(normalizeText);
  const allowedTerms = (policy?.allowedTerms ?? []).map(normalizeText);
  const errors = [];

  if (blockedTerms.some(term => term && text.includes(term))) errors.push('blocked_topic');
  if (!allowedTerms.some(term => term && text.includes(term))) errors.push('outside_niche');

  return { ok: errors.length === 0, errors };
}

export function idempotencyKey(date, topic, slug) {
  return `${String(date ?? '').trim()}|${normalize(topic)}|${normalize(slug)}`;
}

export function validateArticle(article = {}, existingPosts = [], policy = {}) {
  const errors = [];
  const limits = policy.limits ?? {};
  const required = ['titulo', 'slug', 'meta_title', 'meta_description', 'focus_keyword', 'categoria', 'content'];

  for (const key of required) {
    if (!String(article[key] ?? '').trim()) errors.push(`missing_${key}`);
  }

  const metaTitleLength = String(article.meta_title ?? '').trim().length;
  if (metaTitleLength < limits.titleMin || metaTitleLength > limits.titleMax) {
    errors.push('invalid_meta_title_length');
  }

  const descriptionLength = String(article.meta_description ?? '').trim().length;
  if (descriptionLength < limits.descriptionMin || descriptionLength > limits.descriptionMax) {
    errors.push('invalid_meta_description_length');
  }

  const tags = Array.isArray(article.tags) ? article.tags : [];
  if (tags.length > limits.tagsMax) errors.push('too_many_tags');

  const faq = Array.isArray(article.faq) ? article.faq : [];
  if (faq.length < limits.faqMin) errors.push('insufficient_faq');

  const sources = Array.isArray(article.fuentes) ? article.fuentes : [];
  const secureSources = sources.filter(source => /^https:\/\//i.test(String(source?.url ?? source)));
  if (secureSources.length < limits.sourcesMin) errors.push('insufficient_sources');

  const content = String(article.content ?? '');
  if (!(policy.commercialUrls ?? []).some(url => content.includes(url))) {
    errors.push('missing_commercial_link');
  }

  const isDuplicate = (Array.isArray(existingPosts) ? existingPosts : []).some(post => (
    normalize(post?.slug) === normalize(article.slug)
      || similarity(post?.title, article.titulo) >= limits.duplicateSimilarity
  ));
  if (isDuplicate) errors.push('duplicate_article');

  return { ok: errors.length === 0, errors };
}


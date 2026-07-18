import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const workflowPath = join(root, 'auto-post-doble3d.json');
const workflow = JSON.parse(readFileSync(workflowPath, 'utf8'));
const findNode = name => workflow.nodes.find(item => item.name === name);
const connection = node => ({ main: [[{ node, type: 'main', index: 0 }]] });
const codeNode = (id, name, position, jsCode) => ({
  id,
  name,
  type: 'n8n-nodes-base.code',
  typeVersion: 2,
  position,
  parameters: { jsCode },
});

const wpAuthorization = findNode('Publicar en WordPress').parameters.headerParameters.parameters
  .find(header => header.name === 'Authorization').value;

const topicNode = findNode('Elegir tema');
topicNode.position = [-900, 0];
topicNode.parameters.jsCode = topicNode.parameters.jsCode.replace(
  'return [{ json: { tema: tema.label, query: tema.query, rssUrl } }];',
  'return [{ json: { topic: tema.label, tema: tema.label, query: tema.query, rssUrl } }];',
);

findNode('Martes y Jueves 11:00').position = [-1120, 0];
findNode('Ejecución manual (test)').position = [-1120, 180];
findNode('Noticias RSS').position = [-240, 0];
findNode('Preparar prompt').position = [-20, 0];
findNode('DeepSeek (redactar)').position = [200, 0];
findNode('Generar imagen').position = [1740, 0];
findNode('Subir imagen a WP').position = [1960, 0];
findNode('Alt de la imagen').position = [2180, 0];
findNode('Publicar en WordPress').position = [2400, 0];

const preparePrompt = findNode('Preparar prompt');
preparePrompt.parameters.jsCode = preparePrompt.parameters.jsCode
  .split('\n')
  .filter(line => !line.includes('"titulo_imagen"') && !line.includes('"fuentes"'))
  .map(line => {
    if (line.startsWith('\'  "titulo":')) return '\'  "titulo": "máx 65 caracteres, atractivo, incluye la keyword o parte de ella",\',';
    if (line.startsWith('\'  "tags":')) return '\'  "tags": ["3 a 5 tags en español"],\',';
    return line;
  })
  .join('\n');
preparePrompt.parameters.jsCode = preparePrompt.parameters.jsCode
  .replaceAll('content_html', 'content')
  .replace(
    "'  \"titulo\": \"máx 65 caracteres, atractivo, incluye la keyword o parte de ella\",',",
    "'  \"titulo\": \"máx 65 caracteres, atractivo, incluye la keyword o parte de ella\",',\n'  \"titulo_imagen\": \"título visual breve, máx 65 caracteres\",',",
  )
  .replace(
    "'  \"tags\": [\"3 a 5 tags en español\"],',",
    "'  \"tags\": [\"3 a 5 tags en español\"],',\n'  \"fuentes\": [{\"titulo\":\"Nombre de la fuente\",\"url\":\"https://example.org/documento\",\"fecha\":\"2026-07-01\",\"medio\":\"Organización responsable\"}],',",
  )
  .replace(
    '(No hay noticias disponibles hoy: escribe un artículo educativo/evergreen sobre el tema, SIN citar fuentes externas ni inventar URLs.)',
    '(No hay fuentes verificables disponibles hoy: no inventes URLs. La ejecución debe detenerse si no puede incluir al menos una fuente HTTPS real.)',
  );

const normalizeHelpers = String.raw`
const normalize = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
const tokenSet = value => new Set(normalize(value).split('-').filter(Boolean));
const similarity = (left, right) => {
  const a = tokenSet(left); const b = tokenSet(right);
  const both = [...a].filter(value => b.has(value)).length;
  const all = new Set([...a, ...b]).size;
  return all ? both / all : 0;
};`;

const candidateCode = String.raw`${normalizeHelpers}
const topicData = $('Elegir tema').item.json;
const topicText = normalize(topicData.topic).replace(/-/g, ' ');
const allowed = ['realidad virtual','vr','xr','realidad mixta','animacion 3d','scorm','core','capacitacion','mineria','industrial','seguridad','gemelo digital'];
const blocked = ['iphone','ipad','macbook','switch 2','cirugia','gaming de consumo','precios de celulares'];
if (blocked.some(term => topicText.includes(normalize(term).replace(/-/g, ' ')))) throw new Error('OUTSIDE_NICHE: blocked_topic');
if (!allowed.some(term => topicText.includes(normalize(term).replace(/-/g, ' ')))) throw new Error('OUTSIDE_NICHE: outside_niche');

const responseItems = $input.all().map(item => item.json);
const existingPosts = responseItems.flatMap(item => Array.isArray(item) ? item : (Array.isArray(item.body) ? item.body : [item])).filter(item => item && item.id);
const duplicate = existingPosts.find(post => similarity(post.title?.rendered ?? post.title, topicData.topic) >= 0.70);
if (duplicate) throw new Error('DUPLICATE_TOPIC: ' + duplicate.id);
return [{ json: { ...topicData, existingPosts: existingPosts.map(post => ({ id: post.id, slug: post.slug, title: post.title?.rendered ?? post.title })) } }];`;

const validationHelpers = String.raw`
const commercialUrls = [
  'https://doble3d.cl/servicios/realidad-virtual/',
  'https://doble3d.cl/servicios/animacion-3d/',
  'https://doble3d.cl/servicios/core/',
  'https://doble3d.cl/servicios/gamificacion-scorm/'
];
const required = ['titulo','slug','meta_title','meta_description','focus_keyword','categoria','tags','fuentes','faq','excerpt','content','titulo_imagen','subtitulo_imagen','tag_top','tag_1','tag_2'];
const normalize = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
const tokens = value => new Set(normalize(value).split('-').filter(Boolean));
const similarity = (left, right) => { const a=tokens(left), b=tokens(right); const both=[...a].filter(value=>b.has(value)).length; const all=new Set([...a,...b]).size; return all ? both/all : 0; };
const parseResponse = response => {
  const choice = (response.choices && response.choices[0]) || {};
  let raw = (choice.message && choice.message.content) || '';
  raw = raw.replace(/^\s*\`\`\`(?:json)?/i, '').replace(/\`\`\`\s*$/i, '').trim();
  const start = raw.indexOf('{'); const end = raw.lastIndexOf('}');
  if (start >= 0 && end > start) raw = raw.slice(start, end + 1);
  try { return JSON.parse(raw); } catch { throw new Error('INVALID_MODEL_JSON'); }
};
const validate = (article, existingPosts) => {
  const errors = [];
  for (const key of required) if (!Object.hasOwn(article, key) || (typeof article[key] === 'string' && !article[key].trim())) errors.push('missing_' + key);
  const titleLength = String(article.meta_title ?? '').trim().length;
  const descriptionLength = String(article.meta_description ?? '').trim().length;
  if (titleLength < 35 || titleLength > 60) errors.push('invalid_meta_title_length');
  if (descriptionLength < 120 || descriptionLength > 155) errors.push('invalid_meta_description_length');
  if (!Array.isArray(article.tags) || article.tags.length > 5) errors.push('invalid_tags');
  if (!Array.isArray(article.faq) || article.faq.length < 3) errors.push('insufficient_faq');
  const validSources = Array.isArray(article.fuentes) && article.fuentes.some(source => source && /^https:\/\//i.test(source.url ?? '') && source.titulo && source.fecha && source.medio);
  if (!validSources) errors.push('invalid_sources');
  if (!commercialUrls.some(url => String(article.content ?? '').includes(url))) errors.push('missing_commercial_link');
  if (!/^[a-z0-9]+(?:-[a-z0-9]+){2,5}$/.test(String(article.slug ?? ''))) errors.push('invalid_slug');
  if (existingPosts.some(post => normalize(post.slug) === normalize(article.slug) || similarity(post.title, article.titulo) >= 0.70)) errors.push('duplicate_article');
  return [...new Set(errors)];
};`;

const initialValidatorCode = String.raw`${validationHelpers}
const article = parseResponse($json);
const existingPosts = $('Validar candidato').item.json.existingPosts ?? [];
const validationErrors = validate(article, existingPosts);
if (!validationErrors.length) return [{ json: { article, needsCorrection: false, validationErrors: [] } }];

const correctionBody = {
  model: 'deepseek-v4-pro',
  messages: [
    { role: 'system', content: 'Corrige el objeto para cumplir todas las reglas. Devuelve únicamente JSON, conserva hechos y fuentes reales, no inventes URLs.' },
    { role: 'user', content: 'Errores: ' + validationErrors.join(', ') + '\nObjeto: ' + JSON.stringify(article) }
  ],
  response_format: { type: 'json_object' },
  temperature: 0.2,
  max_tokens: 12000,
  thinking: { type: 'disabled' }
};
return [{ json: { article, needsCorrection: true, validationErrors, correctionBody } }];`;

const correctedValidatorCode = String.raw`${validationHelpers}
const article = parseResponse($json);
const existingPosts = $('Validar candidato').item.json.existingPosts ?? [];
const validationErrors = validate(article, existingPosts);
if (validationErrors.length) throw new Error('ARTICLE_INVALID_AFTER_CORRECTION: ' + validationErrors.join(','));
return [{ json: { article, needsCorrection: false, validationErrors: [] } }];`;

const searchDuplicate = {
  id: 'a1b2c3d4-0101-4000-8000-buscardupl001',
  name: 'Buscar duplicado',
  type: 'n8n-nodes-base.httpRequest',
  typeVersion: 4.2,
  position: [-680, 0],
  parameters: {
    method: 'GET',
    url: 'https://doble3d.cl/wp-json/wp/v2/posts',
    sendHeaders: true,
    headerParameters: { parameters: [{ name: 'Authorization', value: wpAuthorization }] },
    sendQuery: true,
    queryParameters: { parameters: [
      { name: 'search', value: '={{ $json.topic }}' },
      { name: 'per_page', value: '20' },
      { name: 'status', value: 'publish,draft,pending,private' },
      { name: '_fields', value: 'id,slug,title' },
    ] },
    options: { timeout: 60000 },
  },
};

const validateCandidate = codeNode('a1b2c3d4-0102-4000-8000-validcand001', 'Validar candidato', [-460, 0], candidateCode);
const validateArticle = codeNode('a1b2c3d4-0103-4000-8000-validart0001', 'Validar artículo', [420, 0], initialValidatorCode);
const correctionIf = {
  id: 'a1b2c3d4-0104-4000-8000-ifcorrect001',
  name: '¿Requiere corrección?',
  type: 'n8n-nodes-base.if',
  typeVersion: 2.2,
  position: [640, 0],
  parameters: { conditions: { options: { caseSensitive: true, leftValue: '', typeValidation: 'strict' }, conditions: [{ id: 'needs-correction', leftValue: '={{ $json.needsCorrection }}', rightValue: true, operator: { type: 'boolean', operation: 'true', singleValue: true } }], combinator: 'and' }, options: {} },
};

const correctionRequest = structuredClone(findNode('DeepSeek (redactar)'));
correctionRequest.id = 'a1b2c3d4-0105-4000-8000-deepcorr0001';
correctionRequest.name = 'DeepSeek (corregir)';
correctionRequest.position = [860, -100];
correctionRequest.parameters.jsonBody = '={{ JSON.stringify($json.correctionBody) }}';

const validateCorrected = codeNode('a1b2c3d4-0106-4000-8000-validcorr001', 'Validar artículo corregido', [1080, -100], correctedValidatorCode);
const approvedArticle = codeNode('a1b2c3d4-0107-4000-8000-artapprove01', 'Artículo aprobado', [1080, 100], 'return $input.all();');

const searchSlug = {
  id: 'a1b2c3d4-0108-4000-8000-buscarslug01',
  name: 'Buscar slug final',
  type: 'n8n-nodes-base.httpRequest',
  typeVersion: 4.2,
  position: [1300, 0],
  parameters: {
    method: 'GET',
    url: 'https://doble3d.cl/wp-json/wp/v2/posts',
    sendHeaders: true,
    headerParameters: { parameters: [{ name: 'Authorization', value: wpAuthorization }] },
    sendQuery: true,
    queryParameters: { parameters: [
      { name: 'slug', value: '={{ $json.article.slug }}' },
      { name: 'per_page', value: '1' },
      { name: 'status', value: 'publish,draft,pending,private' },
      { name: '_fields', value: 'id,slug' },
    ] },
    options: { timeout: 60000 },
  },
};

const confirmSlugCode = String.raw`const responseItems = $input.all().map(item => item.json);
const matches = responseItems.flatMap(item => Array.isArray(item) ? item : (Array.isArray(item.body) ? item.body : [item])).filter(item => item && item.id);
if (matches.length) throw new Error('DUPLICATE_SLUG: ' + matches[0].id);
const corrected = $('Validar artículo corregido').isExecuted;
const source = corrected ? $('Validar artículo corregido').item.json : $('Artículo aprobado').item.json;
return [{ json: source }];`;
const confirmSlug = codeNode('a1b2c3d4-0109-4000-8000-confirmslug1', 'Confirmar slug final', [1520, 0], confirmSlugCode);

const assemble = findNode('Ensamblar post');
assemble.position = [1740, 180];
const oldCode = assemble.parameters.jsCode;
const catsStart = oldCode.indexOf('const CATS =');
const catsEnd = oldCode.indexOf('// Resolver tags:');
const tagStart = oldCode.indexOf('// Resolver tags:');
const tagEnd = oldCode.indexOf('// Schema JSON-LD');
const schemaStart = oldCode.indexOf('// Schema JSON-LD');
const imageVarsStart = oldCode.indexOf('const subt =');
const returnStart = oldCode.indexOf('return [{ json: {', imageVarsStart);
const preservedCats = oldCode.slice(catsStart, catsEnd);
const preservedTags = oldCode.slice(tagStart, tagEnd);
const preservedClosing = oldCode.slice(schemaStart, imageVarsStart);
assemble.parameters.jsCode = `// Ensambla únicamente un artículo que ya superó ambas validaciones.\nconst STATUS = 'draft';\nconst WP = 'https://doble3d.cl/wp-json/wp/v2';\nconst AUTH = ${oldCode.match(/const AUTH = .*?;\n/)[0].slice('const AUTH = '.length, -2)};\nconst a = $json.article;\nif (!a) throw new Error('MISSING_VALIDATED_ARTICLE');\n\n${preservedCats}${preservedTags}${preservedClosing}const subt = String(a.subtitulo_imagen || a.focus_keyword).slice(0, 60);\nconst tagTop = String(a.tag_top).slice(0, 32);\nconst tag1 = String(a.tag_1).slice(0, 18);\nconst tag2 = String(a.tag_2).slice(0, 18);\n\nreturn [{ json: {\n  status: STATUS,\n  titulo: a.titulo.trim(),\n  slug: a.slug,\n  metaTitle: a.meta_title.trim(),\n  metaDesc: a.meta_description.trim(),\n  focusKeyword: a.focus_keyword.trim(),\n  excerpt: a.excerpt.trim(),\n  categoria: catId,\n  tagIds,\n  content: a.content.trim() + '\\n' + bloqueCierre,\n  sources: a.fuentes,\n  imageUrl: 'https://doble3d.cl/image-gen.php?titulo=' + encodeURIComponent(a.titulo_imagen) + '&subtitulo=' + encodeURIComponent(subt) + '&tag_top=' + encodeURIComponent(tagTop) + '&tag_1=' + encodeURIComponent(tag1) + '&tag_2=' + encodeURIComponent(tag2),\n  filename: a.slug + '.webp',\n} }];`;

workflow.nodes = workflow.nodes.filter(item => ![
  'Buscar duplicado', 'Validar candidato', 'Validar artículo', '¿Requiere corrección?',
  'DeepSeek (corregir)', 'Validar artículo corregido', 'Artículo aprobado',
  'Buscar slug final', 'Confirmar slug final',
].includes(item.name));
workflow.nodes.push(searchDuplicate, validateCandidate, validateArticle, correctionIf, correctionRequest, validateCorrected, approvedArticle, searchSlug, confirmSlug);

workflow.connections = {
  'Martes y Jueves 11:00': connection('Elegir tema'),
  'Ejecución manual (test)': connection('Elegir tema'),
  'Elegir tema': connection('Buscar duplicado'),
  'Buscar duplicado': connection('Validar candidato'),
  'Validar candidato': connection('Noticias RSS'),
  'Noticias RSS': connection('Preparar prompt'),
  'Preparar prompt': connection('DeepSeek (redactar)'),
  'DeepSeek (redactar)': connection('Validar artículo'),
  'Validar artículo': connection('¿Requiere corrección?'),
  '¿Requiere corrección?': { main: [[{ node: 'DeepSeek (corregir)', type: 'main', index: 0 }], [{ node: 'Artículo aprobado', type: 'main', index: 0 }]] },
  'DeepSeek (corregir)': connection('Validar artículo corregido'),
  'Validar artículo corregido': connection('Buscar slug final'),
  'Artículo aprobado': connection('Buscar slug final'),
  'Buscar slug final': connection('Confirmar slug final'),
  'Confirmar slug final': connection('Ensamblar post'),
  'Ensamblar post': connection('Generar imagen'),
  'Generar imagen': connection('Subir imagen a WP'),
  'Subir imagen a WP': connection('Alt de la imagen'),
  'Alt de la imagen': connection('Publicar en WordPress'),
};

workflow.active = false;
writeFileSync(workflowPath, `${JSON.stringify(workflow, null, 2)}\n`, 'utf8');
console.log(`Consolidated ${workflow.nodes.length} nodes in ${workflowPath}`);

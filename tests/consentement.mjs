#!/usr/bin/env node
// Vérifie EM Consent dans un vrai navigateur (Chromium, Playwright), sans session ni témoin :
//   1. aucun domaine tiers avant le choix, bannière visible, aucun témoin em_consent ;
//   2. bannière atteinte et refusée au clavier ; toujours aucun domaine tiers, même après rechargement ;
//   3. le lien « #em-consent » rouvre les préférences, au clavier ; accepter la mesure charge GTM et GA4 ;
//   4. retirer la mesure recharge la page sans GTM ni GA4 ;
//   5. les textes de la page anglaise sont en anglais ;
//   6. axe-core : aucune violation WCAG A/AA dans la bannière ni dans les préférences.
//
//   node tests/consentement.mjs http://site.local/page-de-test/ [http://site.local/en/test-page/]
//
// La page doit contenir le lien de gestion (#em-consent) ; GTM et GA4 doivent être réglés.
import { chromium } from 'playwright';
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const [URL_FR, URL_EN] = process.argv.slice(2);
if (!URL_FR) {
  console.error('usage : node tests/consentement.mjs <url-fr> [<url-en>]');
  process.exit(1);
}
const HOTE = new URL(URL_FR).hostname;
const AXE = readFileSync(createRequire(import.meta.url).resolve('axe-core/axe.min.js'), 'utf8');

let echecs = 0;
const ok = (cond, msg) => {
  console.log(`${cond ? '✓' : '✗'} ${msg}`);
  if (!cond) echecs++;
};

const navigateur = await chromium.launch();
const contexte = await navigateur.newContext({ viewport: { width: 1280, height: 800 } });
const page = await contexte.newPage();
let requetes = [];
page.on('request', (r) => requetes.push(r.url()));
const tiers = () => [...new Set(requetes.map((u) => new URL(u)).filter((u) => /^https?:$/.test(u.protocol) && u.hostname !== HOTE).map((u) => u.hostname))];
const google = () => requetes.filter((u) => /googletagmanager\.com\/(gtm\.js|gtag\/js)/.test(u));
const temoin = async () => (await contexte.cookies()).find((c) => c.name === 'em_consent');
const actif = () => page.evaluate(() => {
  const el = document.activeElement;
  return { id: el.id, texte: (el.textContent || '').trim(), action: el.getAttribute('data-em-consent-action'), dansBanniere: !!el.closest('#em-consent'), dansDialogue: !!el.closest('#em-consent-preferences') };
});
const axe = async (selecteur) => {
  await page.addScriptTag({ content: AXE });
  const r = await page.evaluate((s) => window.axe.run(s, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'] }), selecteur);
  return r.violations.map((v) => `${v.id} (${v.nodes.length})`);
};

// 1. Premier affichage.
await page.goto(URL_FR, { waitUntil: 'networkidle' });
ok(tiers().length === 0, `aucun domaine tiers avant le choix${tiers().length ? ' : ' + tiers().join(', ') : ''}`);
ok(await page.isVisible('#em-consent'), 'bannière visible');
ok(!(await temoin()), 'aucun témoin em_consent avant le choix');
ok((await page.evaluate(() => JSON.stringify(window.dataLayer))).includes('"default"'), 'mode de consentement : valeurs par défaut envoyées');
const violationsBanniere = await axe('#em-consent');
ok(violationsBanniere.length === 0, `axe-core, bannière : ${violationsBanniere.join(', ') || 'aucune violation'}`);

// 2. Clavier : Tab jusqu'à la bannière, puis jusqu'à « Refuser ».
let el;
for (let i = 0; i < 6; i++) {
  await page.keyboard.press('Tab');
  el = await actif();
  if (el.dansBanniere) break;
}
ok(el.dansBanniere, `la bannière est atteinte au clavier (${el.texte})`);
for (let i = 0; i < 4 && el.action !== 'refuser'; i++) {
  await page.keyboard.press('Tab');
  el = await actif();
}
ok(el.action === 'refuser', 'le bouton Refuser est atteint au clavier');
await page.keyboard.press('Enter');
await page.waitForTimeout(500);
ok(!(await page.isVisible('#em-consent')), 'Refuser ferme la bannière');
el = await actif();
ok(!el.dansBanniere, 'le focus quitte la bannière fermée');
const t1 = await temoin();
ok(t1 && /%22mesure%22%3Afalse/.test(t1.value), 'témoin em_consent : mesure refusée');
requetes = [];
await page.reload({ waitUntil: 'networkidle' });
ok(tiers().length === 0, `après refus et rechargement, aucun domaine tiers${tiers().length ? ' : ' + tiers().join(', ') : ''}`);
ok(!(await page.isVisible('#em-consent')), 'la bannière ne revient pas après le choix');

// 3. Le lien de gestion rouvre les préférences, au clavier.
await page.focus('a[href$="#em-consent"]');
await page.keyboard.press('Enter');
await page.waitForTimeout(300);
ok(await page.isVisible('#em-consent-preferences'), 'le lien « Gestion des témoins » ouvre les préférences');
el = await actif();
ok(el.dansDialogue, 'le focus est dans les préférences');
const violationsDialogue = await axe('#em-consent-preferences');
ok(violationsDialogue.length === 0, `axe-core, préférences : ${violationsDialogue.join(', ') || 'aucune violation'}`);
await page.keyboard.press('Escape');
await page.waitForTimeout(200);
ok(!(await page.isVisible('#em-consent-preferences')), 'Échap ferme les préférences');
el = await page.evaluate(() => document.activeElement.getAttribute('href'));
ok(el && el.endsWith('#em-consent'), 'le focus revient au lien');

requetes = [];
await page.keyboard.press('Enter');
await page.waitForTimeout(300);
await page.focus('#em-consent-mesure');
await page.keyboard.press('Space');
ok(await page.isChecked('#em-consent-mesure'), 'interrupteur Mesure activé à la barre d’espace');
await page.focus('[data-em-consent-action="enregistrer"]');
await page.keyboard.press('Enter');
await page.waitForTimeout(1500);
const charges = google();
ok(charges.some((u) => u.includes('gtm.js')), 'GTM chargé après l’accord');
ok(charges.some((u) => u.includes('gtag/js')), 'GA4 chargé après l’accord');
const maj = await page.evaluate(() => window.dataLayer.some((e) => e && e[0] === 'consent' && e[1] === 'update' && e[2].analytics_storage === 'granted'));
ok(maj, 'mode de consentement : analytics_storage accordé');

// 4. Retrait : la page se recharge sans GTM ni GA4.
await page.click('a[href$="#em-consent"]');
await page.waitForTimeout(300);
requetes = [];
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle' }), page.click('#em-consent-preferences [data-em-consent-action="refuser"]')]);
ok(tiers().length === 0, `après retrait du consentement, aucun domaine tiers${tiers().length ? ' : ' + tiers().join(', ') : ''}`);

// 5. Page anglaise.
if (URL_EN) {
  const en = await contexte.newPage();
  await contexte.clearCookies();
  await en.goto(URL_EN, { waitUntil: 'networkidle' });
  const texte = await en.textContent('#em-consent');
  ok(/Accept all/.test(texte) && /Reject all/.test(texte), 'bannière en anglais sur la page anglaise');
  const fr = await page.textContent('#em-consent');
  ok(/Tout accepter/.test(fr) && /Tout refuser/.test(fr), 'bannière en français sur la page française');
}

await navigateur.close();
console.log(echecs ? `\n${echecs} échec(s)` : '\nTout est vert.');
process.exit(echecs ? 1 : 0);

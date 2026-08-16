/**
 * Google Apps Script → Drive folder "Job share"
 *
 * OUTPUT RULES (must match exactly):
 *   Folder name:     {job title} - {company name}   e.g. "Software Engineer - BRG"
 *   description.txt: job description text ONLY (no Title/Company/User/etc.)
 *   Resume PDF:      Shayne Guiliano_{company name}.pdf
 *
 * Target:
 *   https://drive.google.com/drive/folders/1jnLo3dJbuEPVkLId2ys2UBHXkcYG9Bhy
 *
 * CRITICAL — editing this file in script.google.com does NOTHING until you redeploy:
 *   Deploy → Manage deployments → pencil (Edit) → Version: New version → Deploy
 *   Then open the /exec URL in a browser. You MUST see "script_version":"naming-v2"
 *   If you still see old folders (#222 date — title), you did not redeploy.
 *
 * Full setup:
 * 1. script.google.com → open project → replace ALL of Code.gs with THIS file
 * 2. SHARE_TOKEN must match .env JOB_APPLICATION_SHARE_TOKEN
 * 3. Deploy web app: Execute as Me, Who has access Anyone
 * 4. Paste /exec URL into JOB_APPLICATION_SHARE_WEBHOOK_URL
 * 5. php artisan config:clear && php artisan job-application:test-share --probe-only
 */

var SHARE_TOKEN = 'ats-share-change-me';
var JOB_SHARE_FOLDER_ID = '1jnLo3dJbuEPVkLId2ys2UBHXkcYG9Bhy';
var JOB_SHARE_FOLDER_NAME = 'Job share';

/** Bump this when naming rules change — probe/browser must show this value. */
var SCRIPT_VERSION = 'naming-v2';

function doGet() {
  var folderMeta = safeFolderMeta_();
  return jsonResponse({
    ok: true,
    service: 'ats-job-application-share',
    script_version: SCRIPT_VERSION,
    folder_id: JOB_SHARE_FOLDER_ID,
    folder_name: folderMeta.name || JOB_SHARE_FOLDER_NAME,
    folder_url: folderMeta.url || ('https://drive.google.com/drive/folders/' + JOB_SHARE_FOLDER_ID),
    naming: {
      folder: '{job title} - {company name}',
      description_file: 'description.txt (description only)',
      resume: 'Shayne Guiliano_{company name}.pdf',
    },
    message: 'Webhook is live. Use POST from Laravel.',
  });
}

function doPost(e) {
  try {
    var raw = (e && e.postData && e.postData.contents) ? e.postData.contents : '{}';
    var data = JSON.parse(raw);

    if (!data.token || data.token !== SHARE_TOKEN) {
      return jsonResponse({
        ok: false,
        error: 'Unauthorized: SHARE_TOKEN in Apps Script must equal JOB_APPLICATION_SHARE_TOKEN in .env',
      });
    }

    var root = DriveApp.getFolderById(JOB_SHARE_FOLDER_ID);

    var title = String(data.title || 'Untitled').trim();
    var company = String(data.company_name || '').trim();

    // e.g. "Software Engineer - BRG"
    var folderName = sanitizeDriveName_(
      company ? (title + ' - ' + company) : title
    );
    var appFolder = root.createFolder(folderName);

    // description.txt = description body only — no metadata headers
    var descriptionOnly = String(data.description_text || '');
    appFolder.createFile('description.txt', descriptionOnly, MimeType.PLAIN_TEXT);

    var resumeMeta = null;
    if (data.resume && data.resume.content_base64) {
      var bytes = Utilities.base64Decode(data.resume.content_base64);
      // e.g. "Shayne Guiliano_BRG.pdf"
      var resumeName = company
        ? ('Shayne Guiliano_' + sanitizeDriveName_(company) + '.pdf')
        : 'Shayne Guiliano.pdf';
      var resumeFile = appFolder.createFile(
        Utilities.newBlob(
          bytes,
          data.resume.mime_type || 'application/pdf',
          resumeName
        )
      );
      resumeMeta = {
        id: resumeFile.getId(),
        url: resumeFile.getUrl(),
        name: resumeFile.getName(),
      };
    }

    return jsonResponse({
      ok: true,
      script_version: SCRIPT_VERSION,
      parent_folder_id: root.getId(),
      parent_folder_name: root.getName(),
      parent_folder_url: root.getUrl(),
      folder_name: folderName,
      folder_id: appFolder.getId(),
      folder_url: appFolder.getUrl(),
      description_file: 'description.txt',
      resume: resumeMeta,
    });
  } catch (err) {
    return jsonResponse({ ok: false, error: String(err), script_version: SCRIPT_VERSION });
  }
}

function sanitizeDriveName_(name) {
  return String(name || '')
    .replace(/[\\/:*?"<>|]/g, '-')
    .replace(/\s+/g, ' ')
    .trim()
    .substring(0, 120) || 'Untitled';
}

function jsonResponse(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}

function safeFolderMeta_() {
  try {
    var folder = DriveApp.getFolderById(JOB_SHARE_FOLDER_ID);
    return { name: folder.getName(), url: folder.getUrl() };
  } catch (err) {
    return { name: null, url: null, error: String(err) };
  }
}

function testDriveAccess() {
  var folder = DriveApp.getFolderById(JOB_SHARE_FOLDER_ID);
  Logger.log('OK folder name: ' + folder.getName());
  Logger.log('OK folder url: ' + folder.getUrl());
  Logger.log('script_version: ' + SCRIPT_VERSION);
}

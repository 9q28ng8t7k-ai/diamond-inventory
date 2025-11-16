<?php
// index.php：單頁前端 + 呼叫 /api/*.php
?><!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <title>鑽石原料管理（3D 視圖版）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Noto Sans CJK TC", "PingFang TC", sans-serif;
      background: #f3f4f6;
      color: #111827;
    }
    h1 {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    header {
      padding: 16px 24px;
      border-bottom: 1px solid #e5e7eb;
      background: #ffffff;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    header .logo-dot {
      display: inline-flex;
      width: 20px;
      height: 20px;
      border-radius: 999px;
      background: #2563eb;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 12px;
      font-weight: 700;
    }
    header .sub {
      font-size: 12px;
      color: #6b7280;
      margin-top: 4px;
    }

    main {
      padding: 16px 24px 24px;
      display: grid;
      grid-template-columns: minmax(0, 2.1fr) minmax(0, 1.5fr);
      gap: 16px;
    }

    .card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(15,23,42,0.06);
      padding: 16px 18px 18px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .card-title {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      font-size: 14px;
      font-weight: 600;
    }
    .card-title .sub {
      font-size: 11px;
      color: #9ca3af;
      font-weight: 400;
    }

    form .field {
      display: flex;
      flex-direction: column;
      margin-bottom: 8px;
    }
    form .inline-fields {
      display: flex;
      gap: 12px;
    }
    form label {
      font-size: 12px;
      color: #4b5563;
      margin-bottom: 2px;
    }
    .req {
      color: #ef4444;
      margin-left: 2px;
    }
    input[type="text"],
    input[type="number"],
    textarea {
      border-radius: 999px;
      border: 1px solid #e5e7eb;
      padding: 6px 10px;
      font-size: 13px;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s;
      background: #f9fafb;
    }
    textarea {
      border-radius: 12px;
      resize: vertical;
      min-height: 48px;
    }
    input:focus, textarea:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 1px rgba(37,99,235,0.25);
      background: #ffffff;
    }

    .hint {
      font-size: 11px;
      color: #9ca3af;
    }

    .btn-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 6px;
      gap: 8px;
    }

    button {
      border-radius: 999px;
      border: none;
      padding: 6px 16px;
      font-size: 13px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      transition: background 0.15s, box-shadow 0.15s, transform 0.05s;
      white-space: nowrap;
    }
    button.primary {
      background: #2563eb;
      color: #ffffff;
      box-shadow: 0 8px 16px rgba(37,99,235,0.3);
    }
    button.primary:hover {
      background: #1d4ed8;
    }
    button.outline {
      background: #ffffff;
      color: #374151;
      border: 1px solid #d1d5db;
    }
    button.outline:hover {
      background: #f3f4f6;
    }
    button.danger {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fecaca;
      padding: 4px 10px;
      font-size: 11px;
    }
    button.danger:hover {
      background: #fecaca;
    }
    button:active {
      transform: translateY(1px);
      box-shadow: none;
    }

    .error {
      font-size: 12px;
      color: #b91c1c;
      margin-top: 4px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }
    th, td {
      padding: 6px 8px;
      text-align: left;
      border-bottom: 1px solid #e5e7eb;
    }
    th {
      font-size: 11px;
      font-weight: 600;
      color: #6b7280;
      background: #f9fafb;
    }
    tbody tr {
      cursor: pointer;
    }
    tbody tr:hover {
      background: #eff6ff;
    }
    tbody tr.selected {
      background: #dbeafe;
    }
    td.actions {
      text-align: right;
      white-space: nowrap;
    }

    .empty {
      font-size: 12px;
      color: #9ca3af;
      padding: 12px 4px;
    }

    .detail-row {
      font-size: 12px;
      display: flex;
      justify-content: space-between;
      margin-bottom: 2px;
    }
    .detail-label {
      color: #6b7280;
    }

    #three-container {
      position: relative;
      width: 100%;
      height: 260px;
      border-radius: 14px;
      background: radial-gradient(circle at top left, #eff6ff, #e5e7eb);
      overflow: hidden;
    }
    #dim-labels {
      position: absolute;
      left: 12px;
      bottom: 10px;
      font-size: 11px;
      background: rgba(15, 23, 42, 0.76);
      color: #e5e7eb;
      padding: 3px 8px;
      border-radius: 999px;
    }
    #dim-labels code {
      color: #93c5fd;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    .log-item {
      border-bottom: 1px dashed #e5e7eb;
      padding: 6px 4px;
      font-size: 12px;
    }
    .log-item:last-child {
      border-bottom: none;
    }
    .log-item .time {
      color: #6b7280;
      font-size: 11px;
    }
    .log-item .qty {
      font-weight: 500;
      color: #111827;
    }
    .log-item .purpose {
      color: #374151;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 11px;
      border-radius: 999px;
      padding: 2px 8px;
    }
    .badge.blue {
      background: #eff6ff;
      color: #1d4ed8;
    }

    @media (max-width: 960px) {
      main {
        grid-template-columns: minmax(0, 1fr);
      }
    }
  </style>

  <!-- three.js CDN，如果 NAS 沒外網，之後可以改成本地檔案 -->
  <script src="https://unpkg.com/three@0.160.0/build/three.min.js"></script>
</head>
<body>
<header>
  <h1>
    <span class="logo-dot">D</span>
    鑽石原料庫存（含 3D 視圖）
  </h1>
  <div class="sub">依廠商／尺寸（L×W×H）管理，每批可登記領用紀錄。</div>
</header>

<main>
  <!-- 左邊：新增＋批次列表 -->
  <section class="left card">
    <div class="card-title">
      <span>新增 / 編輯原料</span>
      <span class="sub">尺寸格式：例如 <code>3.5x3.5x1.0</code> 或 <code>6x3x1</code></span>
    </div>

    <form id="add-form">
      <div class="inline-fields">
        <div class="field">
          <label>廠商 <span class="req">*</span></label>
          <input id="f-vendor" type="text" required placeholder="例如：ABC Diamonds" />
        </div>
        <div class="field">
          <label>尺寸 L×W×H <span class="req">*</span></label>
          <input id="f-size" type="text" required placeholder="3.5x3.5x1.0" />
        </div>
      </div>

      <div class="inline-fields" style="margin-top:4px;">
        <div class="field">
          <label>數量（塊） <span class="req">*</span></label>
          <input id="f-qty" type="number" min="0" step="1" required value="1" />
        </div>
        <div class="field">
          <label>單價 / 塊（任意貨幣）</label>
          <input id="f-price" type="number" min="0" step="0.01" />
        </div>
      </div>

      <div class="field">
        <label>備註</label>
        <input id="f-note" type="text" placeholder="用途 / 材質說明…" />
      </div>

      <div class="btn-row">
        <span class="hint">點選下方列表某一筆，可載入到表單進行編輯；刪除不會保留紀錄。</span>
        <div>
          <button type="button" class="outline" id="btn-clear-form">清空</button>
          <button type="submit" class="primary" id="btn-add">儲存批次</button>
        </div>
      </div>

      <div class="error" id="add-error" style="display:none;"></div>
    </form>

    <div class="card" style="margin-top:8px; padding:10px 12px 12px;">
      <div class="card-title">
        <span>原料批次列表</span>
        <span class="sub" id="list-summary">0 筆</span>
      </div>
      <div class="table-wrap" style="margin-top:6px; max-height:280px; overflow:auto;">
        <table>
          <thead>
          <tr>
            <th>廠商</th>
            <th>尺寸（L×W×H）</th>
            <th>庫存數量</th>
            <th>已領出</th>
            <th>單價</th>
            <th>總價</th>
            <th>備註</th>
            <th style="text-align:right;">操作</th>
          </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
        <div id="empty-hint" class="empty">尚無任何批次，請先在上方新增一筆。</div>
      </div>
    </div>
  </section>

  <!-- 右邊：3D + 批次詳細 + 領料紀錄 -->
  <section class="right card">
    <div class="card-title">
      <span id="selected-title">尚未選擇批次</span>
    </div>

    <div id="three-container">
      <div id="dim-labels"><span>L×W×H：-</span></div>
    </div>

    <div style="margin-top:8px;">
      <div class="detail-row">
        <span class="detail-label">廠商</span>
        <span id="info-vendor">-</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">庫存數量</span>
        <span id="info-qty">-</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">單價 / 總價</span>
        <span id="info-price">-</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">備註</span>
        <span id="info-note">-</span>
      </div>
    </div>

    <div class="card" style="margin-top:10px; padding:10px 12px 12px;">
      <div class="card-title">
        <span>領料紀錄</span>
        <span class="badge blue">
          <span style="width:6px;height:6px;border-radius:999px;background:#1d4ed8;"></span>
          針對目前選取批次
        </span>
      </div>

      <form id="withdraw-form" style="margin-top:4px;">
        <div class="inline-fields">
          <div class="field">
            <label>領出數量</label>
            <input id="w-qty" type="number" min="1" step="1" />
          </div>
          <div class="field" style="flex:1;">
            <label>用途</label>
            <input id="w-purpose" type="text" placeholder="例如：FC-200 粗磨用 / 某刀具雷射半切…" />
          </div>
        </div>
        <div class="btn-row" style="margin-top:6px;">
          <div class="error" id="w-error" style="display:none;"></div>
          <button type="submit" class="primary" style="margin-left:auto;">登記領料</button>
        </div>
      </form>

      <div id="log-list" style="margin-top:6px;">
        <div class="empty">尚無領料紀錄。</div>
      </div>
    </div>
  </section>
</main>

<script>
  // ===== 全域狀態 =====
  let items = [];
  let selectedId = null;

  let scene = null;
  let camera = null;
  let renderer = null;
  let boxMesh = null;
  let threeContainer = null;

  // ===== 小工具 =====
  function parseSize(str) {
    if (!str) return null;
    const s = str.toLowerCase().replace(/×/g, 'x');
    const parts = s.split('x').map(p => p.trim()).filter(Boolean);
    if (parts.length !== 3) return null;
    const nums = parts.map(Number);
    if (nums.some(n => !isFinite(n) || n <= 0)) return null;
    return { length: nums[0], width: nums[1], height: nums[2] };
  }

  function formatDateTime(raw) {
    if (!raw) return '';
    return String(raw);
  }

  // ===== 呼叫 API =====
  async function apiListItems() {
    const res = await fetch('/api/items.php');
    if (!res.ok) throw new Error('GET /api/items.php failed');
    return await res.json();
  }

  async function apiSaveItem(payload) {
    const res = await fetch('/api/items.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    if (!res.ok) {
      const txt = await res.text().catch(() => '');
      throw new Error('POST /api/items.php failed: ' + txt);
    }
    return await res.json();
  }

  async function apiDeleteItem(id) {
    const res = await fetch('/api/items.php?id=' + encodeURIComponent(id), {
      method: 'DELETE'
    });
    if (!res.ok) throw new Error('DELETE /api/items.php failed');
    return await res.json();
  }

  async function apiListWithdrawals(itemId) {
    const res = await fetch('/api/withdraw.php?item_id=' + encodeURIComponent(itemId));
    if (!res.ok) throw new Error('GET /api/withdraw.php failed');
    return await res.json();
  }

  async function apiWithdraw(itemId, qty, purpose) {
    const res = await fetch('/api/withdraw.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_id: itemId, qty: qty, purpose: purpose })
    });
    if (!res.ok) {
      const txt = await res.text().catch(() => '');
      throw new Error('POST /api/withdraw.php failed: ' + txt);
    }
    return await res.json();
  }

  async function apiUpdateWithdrawPurpose(id, purpose) {
    const res = await fetch('/api/withdraw.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, purpose: purpose })
    });
    if (!res.ok) {
      const txt = await res.text().catch(() => '');
      throw new Error('PUT /api/withdraw.php failed: ' + txt);
    }
    return await res.json();
  }

  async function refreshItemsFromServer() {
    items = await apiListItems();
    renderList();
    if (selectedId && !items.find(x => x.id === selectedId)) {
      selectedId = null;
    }
    if (!selectedId && items.length > 0) {
      selectedId = items[0].id;
    }
    await renderSelected();
  }

  // ===== three.js =====
  function initThree() {
    threeContainer = document.getElementById('three-container');

    if (!window.THREE) {
      threeContainer.innerHTML = '<div style="padding:10px;font-size:12px;color:#b91c1c;">three.js 載入失敗，請確認 script 路徑。</div>';
      return;
    }

    const w = threeContainer.clientWidth;
    const h = threeContainer.clientHeight;

    scene = new THREE.Scene();
    scene.background = null;

    camera = new THREE.PerspectiveCamera(40, w / h, 0.1, 100);
    // 固定 45 度視角，適合 2~10 mm 尺寸
    camera.position.set(6, 5, 7);
    camera.lookAt(0, 0, 0);

    const ambient = new THREE.AmbientLight(0xffffff, 0.7);
    scene.add(ambient);
    const dir = new THREE.DirectionalLight(0xffffff, 0.8);
    dir.position.set(5, 10, 7);
    scene.add(dir);

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(w, h);
    renderer.setPixelRatio(window.devicePixelRatio || 1);

    const dimLabels = document.getElementById('dim-labels');
    threeContainer.innerHTML = '';
    threeContainer.appendChild(renderer.domElement);
    threeContainer.appendChild(dimLabels);

    animate();
    window.addEventListener('resize', onResize3D);
  }

  function onResize3D() {
    if (!renderer || !camera || !threeContainer) return;
    const w = threeContainer.clientWidth;
    const h = threeContainer.clientHeight;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
  }

  function setBoxSize(L, W, H) {
    if (!scene || !THREE) return;

    if (boxMesh) {
      scene.remove(boxMesh);
      boxMesh.geometry.dispose();
      boxMesh.material.dispose();
      boxMesh = null;
    }

    // 1 mm 對應 0.2 個單位，2~10mm 都能在固定視角下看出大小差異
    const mmScale = 0.2;

    const geo = new THREE.BoxGeometry(
      L * mmScale,
      H * mmScale,
      W * mmScale
    );

    const mat = new THREE.MeshPhongMaterial({
      color: 0x3b82f6,
      transparent: true,
      opacity: 0.9
    });

    boxMesh = new THREE.Mesh(geo, mat);
    boxMesh.rotation.set(0.4, -0.6, 0);
    scene.add(boxMesh);
  }

  function animate() {
    requestAnimationFrame(animate);
    if (renderer && scene && camera) {
      renderer.render(scene, camera);
    }
  }

  // ===== UI：列表 =====
  function renderList() {
    const tbody = document.getElementById('tbody');
    const emptyHint = document.getElementById('empty-hint');
    tbody.innerHTML = '';

    if (!items || items.length === 0) {
      emptyHint.style.display = 'block';
      document.getElementById('list-summary').textContent = '0 筆';
      return;
    }

    emptyHint.style.display = 'none';
    document.getElementById('list-summary').textContent = items.length + ' 筆';

    items.forEach(it => {
      const tr = document.createElement('tr');
      if (it.id === selectedId) tr.classList.add('selected');

      tr.addEventListener('click', e => {
        if (e.target && e.target.dataset && e.target.dataset.action) return;
        selectedId = it.id;
        renderList();
        renderSelected();
      });

      function td(text) {
        const td = document.createElement('td');
        td.textContent = text;
        return td;
      }

      tr.appendChild(td(it.vendor));
      tr.appendChild(td(it.size_str));
      tr.appendChild(td(it.qty));
      tr.appendChild(td(it.withdrawn_qty != null ? it.withdrawn_qty : 0));
      tr.appendChild(td(it.unit_price != null ? it.unit_price : '-'));

      let totalStr = '-';
      if (it.unit_price != null) {
        const t = Number(it.unit_price) * Number(it.qty);
        totalStr = t.toFixed(2);
      }
      tr.appendChild(td(totalStr));
      tr.appendChild(td(it.note || ''));

      const tdActions = document.createElement('td');
      tdActions.className = 'actions';
      const btnDel = document.createElement('button');
      btnDel.type = 'button';
      btnDel.textContent = '刪除';
      btnDel.className = 'danger';
      btnDel.dataset.action = 'delete';
      btnDel.addEventListener('click', async () => {
        if (!confirm(`確定刪除【${it.vendor}】尺寸 ${it.size_str} 這一批？領料紀錄也會一起刪除。`)) return;
        try {
          await apiDeleteItem(it.id);
          if (selectedId === it.id) selectedId = null;
          await refreshItemsFromServer();
        } catch (e2) {
          alert('刪除失敗：' + e2.message);
        }
      });
      tdActions.appendChild(btnDel);
      tr.appendChild(tdActions);

      tbody.appendChild(tr);
    });
  }

  // ===== UI：右側詳細＋領料紀錄 =====
  async function renderSelected() {
    const titleEl = document.getElementById('selected-title');
    const dimSpan = document.getElementById('dim-labels').querySelector('span');
    const infoVendor = document.getElementById('info-vendor');
    const infoQty = document.getElementById('info-qty');
    const infoPrice = document.getElementById('info-price');
    const infoNote = document.getElementById('info-note');
    const logList = document.getElementById('log-list');

    const it = items.find(x => x.id === selectedId);
    if (!it) {
      titleEl.textContent = '尚未選擇批次';
      dimSpan.textContent = 'L×W×H：-';
      infoVendor.textContent = '-';
      infoQty.textContent = '-';
      infoPrice.textContent = '-';
      infoNote.textContent = '-';
      logList.innerHTML = '<div class="empty">尚無領料紀錄。</div>';
      if (boxMesh && scene) {
        scene.remove(boxMesh);
        boxMesh.geometry.dispose();
        boxMesh.material.dispose();
        boxMesh = null;
      }
      return;
    }

    titleEl.textContent = it.vendor + '｜' + it.size_str;
    dimSpan.innerHTML = 'L×W×H：<code>' + it.size_str + '</code>';
    infoVendor.textContent = it.vendor;
    infoQty.textContent = it.qty + ' 塊';

    if (it.unit_price != null) {
      const total = (Number(it.unit_price) * Number(it.qty)).toFixed(2);
      infoPrice.textContent = it.unit_price + ' / 塊，總價 ' + total;
    } else {
      infoPrice.textContent = '-';
    }
    infoNote.textContent = it.note || '-';

    if (it.length && it.width && it.height) {
      setBoxSize(Number(it.length), Number(it.width), Number(it.height));
    }

    // 領料紀錄
    try {
      const logs = await apiListWithdrawals(it.id);
      if (!logs || logs.length === 0) {
        logList.innerHTML = '<div class="empty">尚無領料紀錄。</div>';
      } else {
        logList.innerHTML = '';
        logs.forEach(log => {
          const div = document.createElement('div');
          div.className = 'log-item';
          const timeStr = formatDateTime(log.created_at);
          const purposeText = log.purpose || '未填用途';

          div.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span class="time">${timeStr}</span>
              <button type="button"
                      class="outline"
                      style="font-size:10px;padding:2px 8px;border-radius:999px;"
                      data-edit-withdraw-id="${log.id}">
                編輯
              </button>
            </div>
            <div>
              <span class="qty">領出 ${log.qty} 塊</span>
              <span class="purpose">｜${purposeText}</span>
            </div>
          `;
          logList.appendChild(div);
        });

        // 綁定每個「編輯」按鈕
        logList.querySelectorAll('button[data-edit-withdraw-id]').forEach(btn => {
          btn.addEventListener('click', async () => {
            const id = Number(btn.getAttribute('data-edit-withdraw-id'));
            const parent = btn.closest('.log-item');
            const spanPurpose = parent.querySelector('.purpose');
            const current = spanPurpose ? spanPurpose.textContent.replace(/^｜/, '') : '';
            const updated = window.prompt('修改用途說明：', current);
            if (updated === null) return; // 取消
            try {
              await apiUpdateWithdrawPurpose(id, updated);
              await renderSelected(); // 重畫這一塊
            } catch (e2) {
              alert('更新失敗：' + e2.message);
            }
          });
        });
      }
    } catch (e) {
      logList.innerHTML = '<div class="empty" style="color:#b91c1c;">讀取領料紀錄失敗：' + e.message + '</div>';
    }
  }

  // ===== 表單處理 =====
  function clearForm() {
    document.getElementById('f-vendor').value = '';
    document.getElementById('f-size').value = '';
    document.getElementById('f-qty').value = '1';
    document.getElementById('f-price').value = '';
    document.getElementById('f-note').value = '';
    const err = document.getElementById('add-error');
    err.style.display = 'none';
    err.textContent = '';
  }

  async function handleSaveItem(e) {
    e.preventDefault();
    const err = document.getElementById('add-error');
    err.style.display = 'none';
    err.textContent = '';

    const vendor = document.getElementById('f-vendor').value.trim();
    const sizeStr = document.getElementById('f-size').value.trim();
    const qty = Number(document.getElementById('f-qty').value);
    const priceStr = document.getElementById('f-price').value;
    const note = document.getElementById('f-note').value.trim();

    if (!vendor) {
      err.textContent = '廠商必填。';
      err.style.display = 'block';
      return;
    }
    const size = parseSize(sizeStr);
    if (!size) {
      err.textContent = '尺寸格式錯誤，請用 LxWxH，例如 6x3x1。';
      err.style.display = 'block';
      return;
    }
    if (!Number.isInteger(qty) || qty < 0) {
      err.textContent = '數量必須是 0 或以上的整數。';
      err.style.display = 'block';
      return;
    }

    let unitPrice = null;
    if (priceStr) {
      const p = Number(priceStr);
      if (!isFinite(p) || p < 0) {
        err.textContent = '單價格式錯誤。';
        err.style.display = 'block';
        return;
      }
      unitPrice = p;
    }

    const payload = {
      vendor: vendor,
      size_str: sizeStr,
      length: size.length,
      width: size.width,
      height: size.height,
      qty: qty,
      unit_price: unitPrice,
      note: note
    };

    try {
      const saved = await apiSaveItem(payload);
      await refreshItemsFromServer();
      selectedId = saved.id;
      clearForm();
    } catch (e2) {
      err.textContent = '儲存失敗：' + e2.message;
      err.style.display = 'block';
    }
  }

  async function handleWithdraw(e) {
    e.preventDefault();
    const err = document.getElementById('w-error');
    err.style.display = 'none';
    err.textContent = '';

    const it = items.find(x => x.id === selectedId);
    if (!it) {
      err.textContent = '請先在左側選擇一個批次。';
      err.style.display = 'block';
      return;
    }

    const qtyInput = document.getElementById('w-qty');
    const purposeInput = document.getElementById('w-purpose');
    const qty = Number(qtyInput.value);
    const purpose = purposeInput.value.trim();

    if (!Number.isInteger(qty) || qty <= 0) {
      err.textContent = '領出數量必須是正整數。';
      err.style.display = 'block';
      return;
    }
    if (qty > it.qty) {
      err.textContent = `庫存只有 ${it.qty} 塊，無法領出 ${qty}。`;
      err.style.display = 'block';
      return;
    }

    try {
      await apiWithdraw(it.id, qty, purpose);
      qtyInput.value = '';
      purposeInput.value = '';
      await refreshItemsFromServer();
    } catch (e2) {
      err.textContent = '領料失敗：' + e2.message;
      err.style.display = 'block';
    }
  }

  // ===== 初始化 =====
  document.addEventListener('DOMContentLoaded', async () => {
    initThree();

    document.getElementById('add-form').addEventListener('submit', handleSaveItem);
    document.getElementById('btn-clear-form').addEventListener('click', clearForm);
    document.getElementById('withdraw-form').addEventListener('submit', handleWithdraw);

    try {
      await refreshItemsFromServer();
    } catch (e) {
      alert('載入資料失敗：' + e.message);
    }
  });
</script>
</body>
</html>

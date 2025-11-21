<?php
// New single-page UI for diamond inventory
?>
<!doctype html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>鑽石原料管理</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Noto Sans TC', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: #0b1221;
      color: #e5e7eb;
    }
    header {
      position: sticky;
      top: 0;
      z-index: 8;
      padding: 16px 20px;
      background: rgba(11, 18, 33, 0.9);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid #1f2937;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .logo {
      display: inline-flex;
      width: 42px;
      height: 42px;
      border-radius: 12px;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      background: linear-gradient(145deg, #3b82f6, #8b5cf6);
      color: #fff;
      box-shadow: 0 8px 18px rgba(59,130,246,0.3);
    }
    h1 { margin: 0; font-size: 22px; font-weight: 700; }
    main {
      display: grid;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1.3fr);
      gap: 16px;
      padding: 16px 20px 32px;
    }
    .card {
      background: #0f172a;
      border: 1px solid #1f2937;
      border-radius: 16px;
      padding: 16px;
      box-shadow: 0 14px 35px rgba(0,0,0,0.35);
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .card h2 {
      margin: 0;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.4px;
      color: #cbd5f5;
    }
    .muted { color: #9ca3af; font-size: 12px; }
    label { font-size: 12px; color: #9ca3af; margin-bottom: 4px; }
    input, select, textarea {
      width: 100%;
      background: #111827;
      border: 1px solid #1f2937;
      border-radius: 10px;
      padding: 8px 10px;
      font-size: 13px;
      color: #e5e7eb;
    }
    input:focus, select:focus, textarea:focus {
      outline: 1px solid #60a5fa;
      border-color: #60a5fa;
    }
    textarea { resize: vertical; }
    .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
    .inline { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; background: #1f2937; border-radius: 999px; font-size: 12px; color: #cbd5f5; }
    .btn {
      border: none; border-radius: 10px; padding: 10px 14px; font-weight: 700; cursor: pointer; color: #0b1221; background: #60a5fa; transition: transform .1s ease, box-shadow .2s ease;
    }
    .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 18px rgba(96,165,250,0.35); }
    .btn.secondary { background: #1f2937; color: #cbd5f5; box-shadow: none; border: 1px solid #2f3b52; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { padding: 8px 6px; text-align: left; border-bottom: 1px solid #1f2937; }
    th { color: #9ca3af; font-weight: 600; font-size: 12px; }
    tr:hover { background: rgba(59,130,246,0.05); cursor: pointer; }
    tr.selected { background: rgba(59,130,246,0.15); }
    .pill { padding: 4px 10px; border-radius: 999px; font-size: 11px; display: inline-block; }
    .pill.green { background: rgba(16,185,129,0.15); color: #34d399; }
    .pill.red { background: rgba(239,68,68,0.15); color: #f87171; }
    .section-split { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 10px; }
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: 10px; }
    .stat-card { background: #111827; border: 1px solid #1f2937; border-radius: 14px; padding: 10px 12px; }
    canvas { background: #0b1221; border-radius: 12px; border: 1px solid #1f2937; }
    .flex { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .error { color: #f87171; font-size: 12px; }
    .success { color: #34d399; font-size: 12px; }
    .subtle { color: #6b7280; font-size: 12px; }
    .badge { background: #1f2937; padding: 6px 8px; border-radius: 10px; font-size: 12px; color: #cbd5f5; }
    .list { max-height: 240px; overflow: auto; border: 1px solid #1f2937; border-radius: 10px; }
    .hist-item { padding: 10px 12px; border-bottom: 1px solid #1f2937; cursor: pointer; }
    .hist-item:hover { background: rgba(255,255,255,0.03); }
    .hist-item.active { background: rgba(96,165,250,0.1); }
    @media (max-width: 1080px) {
      main { grid-template-columns: 1fr; }
    }
  </style>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" defer></script>
</head>
<body>
  <header>
    <div class="logo">DI</div>
    <div>
      <h1>鑽石原料管理面板</h1>
      <div class="muted">批次入庫、領料紀錄、3D 視覺化與匯率聯動</div>
    </div>
  </header>

  <main>
    <section class="card" id="form-card">
      <div class="flex" style="justify-content: space-between; align-items: baseline;">
        <h2>批次新增 / 編輯</h2>
        <div class="muted" id="edit-indicator">目前狀態：新增模式</div>
      </div>
      <form id="item-form">
        <div class="row">
          <div>
            <label>廠商 <span class="error">*</span></label>
            <input name="vendor" required placeholder="供應商名稱" />
          </div>
          <div>
            <label>自訂序號</label>
            <input name="serial_no" placeholder="可選填批次編號" />
          </div>
        </div>
        <div class="inline">
          <label class="muted">形狀：</label>
          <label class="chip"><input type="radio" name="shape" value="box" checked /> 長方體</label>
          <label class="chip"><input type="radio" name="shape" value="cylinder" /> 圓柱</label>
          <button type="button" class="btn secondary" id="clear-btn">清空</button>
          <button type="button" class="btn secondary" id="cancel-edit" style="display:none;">取消編輯</button>
        </div>
        <div class="row" id="box-dims">
          <div><label>長 (L) <span class="error">*</span></label><input name="length" type="number" step="0.01" min="0" /></div>
          <div><label>寬 (W) <span class="error">*</span></label><input name="width" type="number" step="0.01" min="0" /></div>
          <div><label>高 (H) <span class="error">*</span></label><input name="height" type="number" step="0.01" min="0" /></div>
        </div>
        <div class="row" id="cyl-dims" style="display:none;">
          <div><label>直徑 (Ø) <span class="error">*</span></label><input name="diameter" type="number" step="0.01" min="0" /></div>
          <div><label>高度 (H) <span class="error">*</span></label><input name="cyl_height" type="number" step="0.01" min="0" /></div>
        </div>
        <div class="row">
          <div><label>數量 <span class="error">*</span></label><input name="qty" type="number" min="0" value="0" /></div>
          <div><label>材質</label>
            <select name="material">
              <option value="">未指定</option>
              <option value="hpht">HPHT</option>
              <option value="cvd">CVD</option>
            </select>
          </div>
          <div><label>購買日</label><input name="purchase_date" type="date" /></div>
        </div>
        <div class="row">
          <div>
            <label>外幣單價</label>
            <input name="unit_price_foreign" type="number" step="0.01" min="0" />
          </div>
          <div>
            <label>幣別</label>
            <select name="currency">
              <option value="USD">USD</option>
              <option value="CNY">CNY</option>
              <option value="JPY">JPY</option>
              <option value="TWD">TWD</option>
              <option value="CUSTOM">自訂</option>
            </select>
            <input class="subtle" id="custom-currency" placeholder="自訂幣別" style="margin-top:6px; display:none;" />
          </div>
          <div>
            <label>匯率</label>
            <div class="inline">
              <input name="exchange_rate" type="number" step="0.0001" min="0" />
              <button type="button" class="btn secondary" id="fetch-rate">重新取得</button>
            </div>
            <div class="subtle" id="rate-hint"></div>
          </div>
        </div>
        <div class="row">
          <div>
            <label>台幣單價</label>
            <div class="inline">
              <input name="unit_price_twd" type="number" step="0.01" min="0" />
              <button type="button" class="btn secondary" id="recalc">重新換算</button>
            </div>
          </div>
          <div style="grid-column: span 2;">
            <label>備註</label>
            <textarea name="note" rows="2" placeholder="備註或用途說明"></textarea>
          </div>
        </div>
        <div class="flex" style="justify-content: space-between; margin-top:6px;">
          <div id="form-error" class="error"></div>
          <div class="inline">
            <button type="submit" class="btn">送出</button>
          </div>
        </div>
      </form>
    </section>

    <section class="card" id="detail-card">
      <div class="flex" style="justify-content: space-between; align-items: center;">
        <h2>批次詳細與 3D</h2>
        <div class="badge" id="selection-hint">未選取</div>
      </div>
      <div id="detail-empty" class="muted">請從列表選取批次以顯示詳細資料與 3D 視圖。</div>
      <div id="detail-body" style="display:none;">
        <div class="flex">
          <div class="chip" id="detail-size"></div>
          <div class="chip" id="detail-vendor"></div>
          <div class="chip" id="detail-material"></div>
          <div class="chip" id="detail-purchase"></div>
        </div>
        <div class="row">
          <div>
            <div class="muted">庫存狀態</div>
            <div id="detail-stock" style="font-size: 20px; font-weight:700;"></div>
          </div>
          <div>
            <div class="muted">價格</div>
            <div id="detail-price" style="font-size:14px;"></div>
          </div>
          <div>
            <div class="muted">耗用週期</div>
            <div id="detail-cycle"></div>
          </div>
        </div>
        <div class="section-split">
          <div>
            <div class="muted">3D 模型</div>
            <canvas id="canvas3d" width="360" height="260"></canvas>
          </div>
          <div>
            <div class="muted">藍圖 (上/側視)</div>
            <canvas id="blueprint-top" width="360" height="120"></canvas>
            <canvas id="blueprint-side" width="360" height="120" style="margin-top:8px;"></canvas>
          </div>
        </div>
      </div>
    </section>

    <section class="card">
      <div class="flex" style="justify-content: space-between; align-items: baseline;">
        <h2>庫存批次列表</h2>
        <div class="muted">點擊可選取，或使用編輯 / 刪除</div>
      </div>
      <div class="list" id="items-table-wrap"></div>
    </section>

    <section class="card">
      <div class="flex" style="justify-content: space-between; align-items: baseline;">
        <h2>領料紀錄</h2>
        <div id="withdraw-scope" class="badge">未選取</div>
      </div>
      <form id="withdraw-form" class="row">
        <div><label>領出數量</label><input name="withdraw_qty" type="number" min="1" /></div>
        <div><label>用途</label><input name="purpose" placeholder="用於哪個型號 / 用途" /></div>
        <div class="inline" style="align-self: flex-end;">
          <button type="submit" class="btn">提交領料</button>
        </div>
      </form>
      <div id="withdraw-error" class="error"></div>
      <div class="list" id="withdraw-list"></div>
    </section>

    <section class="card">
      <div class="flex" style="justify-content: space-between;">
        <h2>歷史批次 & 型號耗用統計</h2>
        <div class="muted">已用罄批次列表與耗用速度概況</div>
      </div>
      <div class="section-split">
        <div>
          <div class="list" id="history-list"></div>
        </div>
        <div>
          <div class="stat-grid" id="stats-grid"></div>
        </div>
      </div>
    </section>
  </main>

  <script>
    const apiBase = 'api/';
    let state = {
      items: [],
      selected: null,
      editing: null,
      withdrawals: [],
      rateSource: 'auto',
      manualRate: false,
      manualTwd: false,
    };
    const form = document.getElementById('item-form');
    const withdrawForm = document.getElementById('withdraw-form');

    function fmt(num) {
      if (num === null || num === undefined) return '-';
      const n = Number(num);
      return Number.isFinite(n) ? n.toLocaleString(undefined, { maximumFractionDigits: 2 }) : '-';
    }

    function resetForm() {
      form.reset();
      state.editing = null;
      document.querySelector('[name="shape"][value="box"]').checked = true;
      document.getElementById('box-dims').style.display = '';
      document.getElementById('cyl-dims').style.display = 'none';
      document.getElementById('custom-currency').style.display = 'none';
      state.manualRate = false; state.manualTwd = false;
      document.getElementById('rate-hint').textContent = '';
      document.getElementById('form-error').textContent = '';
      document.getElementById('cancel-edit').style.display = 'none';
      document.getElementById('edit-indicator').textContent = '目前狀態：新增模式';
    }

    function currentShape() {
      return document.querySelector('[name="shape"]:checked').value;
    }

    function toggleShapeUI() {
      if (currentShape() === 'box') {
        document.getElementById('box-dims').style.display = '';
        document.getElementById('cyl-dims').style.display = 'none';
      } else {
        document.getElementById('box-dims').style.display = 'none';
        document.getElementById('cyl-dims').style.display = '';
      }
    }

    document.querySelectorAll('[name="shape"]').forEach(r => r.addEventListener('change', toggleShapeUI));
    document.getElementById('clear-btn').addEventListener('click', resetForm);
    document.getElementById('cancel-edit').addEventListener('click', resetForm);

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const payload = serializeItemForm();
      if (!payload) return;
      try {
        const res = await fetch(apiBase + 'items.php', { method: 'POST', body: JSON.stringify(payload) });
        let data;
        try {
          data = await res.json();
        } catch (e) {
          throw new Error('API 回傳格式錯誤 (可能為 404 或伺服器錯誤)');
        }
        if (!res.ok) throw new Error(data.error || 'submit failed');
        await loadItems();
        resetForm();
      } catch (err) {
        console.error(err);
        document.getElementById('form-error').textContent = err.message;
      }
    });

    function serializeItemForm() {
      const vendor = form.vendor.value.trim();
      if (!vendor) { document.getElementById('form-error').textContent = '廠商必填'; return null; }
      const shape = currentShape();
      let length, width, height;
      if (shape === 'box') {
        length = parseFloat(form.length.value) || 0;
        width = parseFloat(form.width.value) || 0;
        height = parseFloat(form.height.value) || 0;
      } else {
        length = parseFloat(form.diameter.value) || 0;
        width = length;
        height = parseFloat(form.cyl_height.value) || 0;
      }
      const qty = parseInt(form.qty.value || '0', 10);
      const currencySelect = form.currency.value;
      const currency = currencySelect === 'CUSTOM' ? document.getElementById('custom-currency').value.trim().toUpperCase() : currencySelect;
      const payload = {
        id: state.editing,
        vendor,
        shape_type: shape,
        length, width, height,
        qty,
        material_type: form.material.value || null,
        purchase_date: form.purchase_date.value || null,
        unit_price_foreign: form.unit_price_foreign.value === '' ? null : parseFloat(form.unit_price_foreign.value),
        currency_code: currency,
        exchange_rate: form.exchange_rate.value === '' ? null : parseFloat(form.exchange_rate.value),
        unit_price_twd: form.unit_price_twd.value === '' ? null : parseFloat(form.unit_price_twd.value),
        note: form.note.value.trim(),
        serial_no: form.serial_no.value.trim() || null,
      };
      return payload;
    }

    async function loadItems() {
      const res = await fetch(apiBase + 'items.php');
      const data = await res.json();
      state.items = data;
      renderItems();
      renderHistory();
      if (state.selected) {
        const latest = state.items.find(i => i.id === state.selected.id);
        state.selected = latest || null;
        updateDetail();
        loadWithdrawals();
      }
    }

    function renderItems() {
      const wrap = document.getElementById('items-table-wrap');
      const rows = state.items.filter(i => Number(i.is_archived) === 0);
      if (!rows.length) { wrap.innerHTML = '<div class="muted" style="padding:10px;">尚無庫存批次</div>'; return; }
      const html = [`<table><thead><tr><th>廠商</th><th>尺寸</th><th>庫存</th><th>已領出</th><th>購買日</th><th>材質</th><th>單價</th><th></th></tr></thead><tbody>`];
      for (const item of rows) {
        const selected = state.selected && state.selected.id === item.id ? 'class="selected"' : '';
        html.push(`<tr data-id="${item.id}" ${selected}>
          <td>${escapeHtml(item.vendor)}</td>
          <td>${escapeHtml(item.size_str)}</td>
          <td>${item.qty}</td>
          <td>${item.withdrawn_qty}</td>
          <td>${item.purchase_date || '-'}</td>
          <td>${item.material_type || '-'}</td>
          <td>${fmt(item.unit_price_twd)}</td>
          <td class="inline" style="gap:6px;">
            <button data-act="edit" class="btn secondary" style="padding:6px 10px;">編輯</button>
            <button data-act="delete" class="btn secondary" style="padding:6px 10px;">刪除</button>
          </td>
        </tr>`);
      }
      html.push('</tbody></table>');
      wrap.innerHTML = html.join('');
      wrap.querySelectorAll('tbody tr').forEach(tr => {
        const id = Number(tr.dataset.id);
        tr.addEventListener('click', (e) => {
          if (e.target.dataset.act) return; // handled by buttons
          const item = state.items.find(i => i.id === id);
          state.selected = item;
          updateDetail();
          loadWithdrawals();
          renderItems();
        });
        tr.querySelector('[data-act="edit"]').addEventListener('click', (e) => {
          e.stopPropagation();
          const item = state.items.find(i => i.id === id);
          startEdit(item);
        });
        tr.querySelector('[data-act="delete"]').addEventListener('click', async (e) => {
          e.stopPropagation();
          if (!confirm('確定刪除此批次與其領料紀錄？')) return;
          await fetch(apiBase + 'items.php?id=' + id, { method: 'DELETE' });
          if (state.selected && state.selected.id === id) state.selected = null;
          await loadItems();
          updateDetail();
        });
      });
    }

    function startEdit(item) {
      state.editing = item.id;
      document.getElementById('edit-indicator').textContent = `目前編輯：#${item.id} ${item.vendor}`;
      document.getElementById('cancel-edit').style.display = '';
      document.querySelector(`[name="shape"][value="${item.shape_type}"]`).checked = true;
      toggleShapeUI();
      if (item.shape_type === 'box') {
        form.length.value = item.length; form.width.value = item.width; form.height.value = item.height;
      } else {
        form.diameter.value = item.length; form.cyl_height.value = item.height;
      }
      form.vendor.value = item.vendor;
      form.qty.value = item.qty;
      form.purchase_date.value = item.purchase_date || '';
      form.material.value = item.material_type || '';
      form.unit_price_foreign.value = item.unit_price_foreign ?? '';
      form.exchange_rate.value = item.exchange_rate ?? '';
      form.unit_price_twd.value = item.unit_price_twd ?? '';
      form.note.value = item.note || '';
      form.serial_no.value = item.serial_no || '';
      const currency = item.currency_code || 'USD';
      if (['USD','CNY','JPY','TWD'].includes(currency)) {
        form.currency.value = currency;
        document.getElementById('custom-currency').style.display = 'none';
      } else {
        form.currency.value = 'CUSTOM';
        document.getElementById('custom-currency').style.display = '';
        document.getElementById('custom-currency').value = currency;
      }
    }

    function renderHistory() {
      const hist = state.items.filter(i => Number(i.is_archived) === 1).sort((a,b)=> (b.depleted_at||'')?.localeCompare(a.depleted_at||''));
      const list = document.getElementById('history-list');
      if (!hist.length) { list.innerHTML = '<div class="muted" style="padding:10px;">尚無歷史批次</div>'; return; }
      list.innerHTML = hist.map(h => `<div class="hist-item" data-id="${h.id}">
        <div class="flex" style="justify-content: space-between;">
          <div>${escapeHtml(h.vendor)} / ${escapeHtml(h.size_str)}</div>
          <div class="pill red">已用罄</div>
        </div>
        <div class="subtle">購買：${h.purchase_date || '-'} ｜ 用罄：${h.depleted_at || '-'} ｜ 週期：${calcCycle(h)} 天</div>
      </div>`).join('');
      list.querySelectorAll('.hist-item').forEach(div => {
        div.addEventListener('click', () => {
          const item = state.items.find(i => i.id === Number(div.dataset.id));
          state.selected = item; renderItems(); updateDetail(); loadWithdrawals();
          list.querySelectorAll('.hist-item').forEach(d => d.classList.remove('active'));
          div.classList.add('active');
        });
      });
      renderStats(hist);
    }

    function renderStats(hist) {
      const grid = document.getElementById('stats-grid');
      if (!hist.length) { grid.innerHTML = '<div class="muted">暫無統計</div>'; return; }
      const map = {};
      hist.forEach(h => {
        const key = h.size_str;
        map[key] = map[key] || { count:0, days:[] };
        map[key].count++;
        const cycle = calcCycle(h);
        if (cycle !== null) map[key].days.push(cycle);
      });
      const cards = Object.entries(map).map(([size, info]) => {
        const avg = info.days.length ? (info.days.reduce((a,b)=>a+b,0)/info.days.length) : null;
        return { size, count: info.count, avg };
      }).sort((a,b)=> b.count - a.count || (a.avg||999) - (b.avg||999));
      grid.innerHTML = cards.map(c => `<div class="stat-card">
        <div class="muted">尺寸 ${escapeHtml(c.size)}</div>
        <div style="font-weight:700; font-size:18px;">批次 ${c.count}</div>
        <div class="subtle">平均用罄天數：${c.avg === null ? '-' : c.avg.toFixed(1)}</div>
      </div>`).join('');
    }

    function calcCycle(item) {
      if (!item.purchase_date || !item.depleted_at) return null;
      const start = new Date(item.purchase_date);
      const end = new Date(item.depleted_at);
      return Math.max(0, Math.round((end - start) / (1000*3600*24)));
    }

    function updateDetail() {
      const hint = document.getElementById('selection-hint');
      if (!state.selected) {
        hint.textContent = '未選取';
        document.getElementById('detail-empty').style.display = '';
        document.getElementById('detail-body').style.display = 'none';
        return;
      }
      const item = state.selected;
      hint.textContent = `選取批次 #${item.id}`;
      document.getElementById('detail-empty').style.display = 'none';
      document.getElementById('detail-body').style.display = '';
      document.getElementById('detail-size').textContent = item.size_str;
      document.getElementById('detail-vendor').textContent = item.vendor;
      document.getElementById('detail-material').textContent = item.material_type || '未指定';
      document.getElementById('detail-purchase').textContent = item.purchase_date || '未填購買日';
      document.getElementById('detail-stock').innerHTML = `庫存 ${item.qty} / 已領 ${item.withdrawn_qty}`;
      const priceText = `外幣 ${fmt(item.unit_price_foreign)} ${item.currency_code || ''} ｜ 匯率 ${fmt(item.exchange_rate)} ｜ 台幣 ${fmt(item.unit_price_twd)}`;
      document.getElementById('detail-price').textContent = priceText;
      const cycle = calcCycle(item);
      document.getElementById('detail-cycle').textContent = cycle === null ? '-' : `${cycle} 天`;
      drawBlueprint(item);
      draw3D(item);
    }

    async function loadWithdrawals() {
      const list = document.getElementById('withdraw-list');
      const badge = document.getElementById('withdraw-scope');
      if (!state.selected) { list.innerHTML = '<div class="muted" style="padding:10px;">請先選取批次</div>'; badge.textContent = '未選取'; return; }
      if (Number(state.selected.is_archived) === 1) { badge.textContent = '已用罄'; }
      else { badge.textContent = `選取批次 #${state.selected.id}`; }
      const res = await fetch(apiBase + 'withdraw.php?item_id=' + state.selected.id);
      state.withdrawals = await res.json();
      if (!state.withdrawals.length) { list.innerHTML = '<div class="muted" style="padding:10px;">尚無領料紀錄</div>'; return; }
      list.innerHTML = state.withdrawals.map(w => `<div class="hist-item" data-id="${w.id}">
        <div class="flex" style="justify-content: space-between;">
          <div>領出 ${w.qty}</div>
          <div class="subtle">${w.created_at || ''}</div>
        </div>
        <div class="inline" style="margin-top:4px;">
          <div class="badge">用途：${escapeHtml(w.purpose || '-') }</div>
          <button class="btn secondary" data-act="edit" style="padding:6px 10px;">編輯</button>
          <button class="btn secondary" data-act="delete" style="padding:6px 10px;">撤回</button>
        </div>
      </div>`).join('');
      list.querySelectorAll('.hist-item').forEach(div => {
        const id = Number(div.dataset.id);
        div.querySelector('[data-act="edit"]').addEventListener('click', () => promptEditWithdraw(id));
        div.querySelector('[data-act="delete"]').addEventListener('click', () => deleteWithdraw(id));
      });
    }

    withdrawForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      document.getElementById('withdraw-error').textContent = '';
      if (!state.selected) { document.getElementById('withdraw-error').textContent = '請先選取批次'; return; }
      if (Number(state.selected.is_archived) === 1) { document.getElementById('withdraw-error').textContent = '已用罄批次不可領料'; return; }
      const qty = parseInt(withdrawForm.withdraw_qty.value || '0', 10);
      if (qty <= 0) { document.getElementById('withdraw-error').textContent = '領出數量需為正整數'; return; }
      if (qty > state.selected.qty) { document.getElementById('withdraw-error').textContent = '領出數量不可超過庫存'; return; }
      const payload = { item_id: state.selected.id, qty, purpose: withdrawForm.purpose.value };
      const res = await fetch(apiBase + 'withdraw.php', { method: 'POST', body: JSON.stringify(payload) });
      const data = await res.json();
      if (!res.ok) { document.getElementById('withdraw-error').textContent = data.error || '失敗'; return; }
      withdrawForm.reset();
      await loadItems();
    });

    async function promptEditWithdraw(id) {
      const w = state.withdrawals.find(x => x.id === id);
      const qty = parseInt(prompt('新數量', w.qty), 10);
      if (!qty || qty <= 0) return;
      const purpose = prompt('用途', w.purpose || '') ?? '';
      const res = await fetch(apiBase + 'withdraw.php', { method: 'PUT', body: JSON.stringify({ id, qty, purpose }) });
      const data = await res.json();
      if (!res.ok) { alert(data.error || '更新失敗'); return; }
      await loadItems();
    }

    async function deleteWithdraw(id) {
      if (!confirm('確定撤回這筆領料？')) return;
      const res = await fetch(apiBase + 'withdraw.php?id=' + id, { method: 'DELETE' });
      const data = await res.json();
      if (!res.ok) { alert(data.error || '失敗'); return; }
      await loadItems();
    }

    document.getElementById('fetch-rate').addEventListener('click', async () => {
      const currencySelect = form.currency.value;
      const currency = currencySelect === 'CUSTOM' ? document.getElementById('custom-currency').value.trim().toUpperCase() : currencySelect;
      const date = form.purchase_date.value;
      const hint = document.getElementById('rate-hint');
      hint.textContent = '查詢匯率中...';
      try {
        // api.exchangerate.host requires key. Use open.er-api.com as free alternative (latest only)
        const url = `https://open.er-api.com/v6/latest/${currency}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error('匯率 API 失敗');
        const data = await res.json();
        const rate = data.rates?.TWD;
        if (!rate) throw new Error('找不到匯率');
        form.exchange_rate.value = rate.toFixed(4);
        hint.textContent = `來源：open.er-api.com`;
        state.manualRate = false;
        recalcTwd();
      } catch (err) {
        hint.textContent = '匯率查詢失敗，可自行輸入';
      }
    });

    document.getElementById('recalc').addEventListener('click', () => { state.manualTwd = false; recalcTwd(); });
    form.exchange_rate.addEventListener('input', () => { state.manualRate = true; });
    form.unit_price_foreign.addEventListener('input', () => { if (!state.manualTwd) recalcTwd(); });
    form.exchange_rate.addEventListener('input', () => { if (!state.manualTwd) recalcTwd(); });
    form.unit_price_twd.addEventListener('input', () => { state.manualTwd = true; });
    form.currency.addEventListener('change', (e) => {
      document.getElementById('custom-currency').style.display = e.target.value === 'CUSTOM' ? '' : 'none';
    });

    function recalcTwd() {
      const foreign = parseFloat(form.unit_price_foreign.value);
      const rate = parseFloat(form.exchange_rate.value);
      if (Number.isFinite(foreign) && Number.isFinite(rate)) {
        form.unit_price_twd.value = (foreign * rate).toFixed(2);
      }
    }

    function escapeHtml(str) {
      const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
      return String(str ?? '').replace(/[&<>"']/g, (s) => map[s]);
    }

    function drawBlueprint(item) {
      const top = document.getElementById('blueprint-top').getContext('2d');
      const side = document.getElementById('blueprint-side').getContext('2d');
      [top, side].forEach(ctx => { ctx.clearRect(0,0,ctx.canvas.width, ctx.canvas.height); ctx.strokeStyle = '#60a5fa'; ctx.lineWidth = 2; ctx.font = '12px sans-serif'; ctx.fillStyle = '#cbd5f5'; });
      const pad = 20;
      if (item.shape_type === 'cylinder') {
        const r = Math.min((top.canvas.height-2*pad)/2, (top.canvas.width-2*pad)/2);
        top.beginPath();
        top.arc(top.canvas.width/2, top.canvas.height/2, r, 0, Math.PI*2);
        top.stroke();
        top.fillText('Ø ' + item.length, pad, pad);
        side.strokeRect(pad, pad, side.canvas.width-2*pad, side.canvas.height-2*pad);
        side.fillText('Ø ' + item.length, pad, pad-6 + 0);
        side.fillText('H ' + item.height, pad, side.canvas.height - 8);
      } else {
        top.strokeRect(pad, pad, top.canvas.width-2*pad, top.canvas.height-2*pad);
        top.fillText('L ' + item.length, pad, pad-6 + 0);
        top.fillText('W ' + item.width, pad, top.canvas.height - 8);
        side.strokeRect(pad, pad, side.canvas.width-2*pad, side.canvas.height-2*pad);
        side.fillText('H ' + item.height, pad, side.canvas.height - 8);
      }
    }

    let renderer, scene, camera, mesh;
    function initThree() {
      const canvas = document.getElementById('canvas3d');
      renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
      renderer.setSize(canvas.clientWidth, canvas.clientHeight);
      scene = new THREE.Scene();
      camera = new THREE.PerspectiveCamera(45, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
      camera.position.set(4,4,4);
      camera.lookAt(0,0,0);
      const light = new THREE.DirectionalLight(0xffffff, 1.1);
      light.position.set(5,5,5);
      scene.add(light);
      scene.add(new THREE.AmbientLight(0x404040));
      animate();
    }

    function animate() {
      requestAnimationFrame(animate);
      if (mesh) mesh.rotation.y += 0.002;
      renderer?.render(scene, camera);
    }

    function draw3D(item) {
      if (!renderer) initThree();
      if (mesh) { scene.remove(mesh); mesh.geometry.dispose(); mesh.material.dispose(); }
      let geometry;
      if (item.shape_type === 'cylinder') {
        geometry = new THREE.CylinderGeometry(item.length/2 || 1, item.length/2 || 1, item.height || 1, 32);
      } else {
        geometry = new THREE.BoxGeometry(item.length || 1, item.height || 1, item.width || 1);
      }
      const material = new THREE.MeshStandardMaterial({ color: 0x60a5fa, transparent: true, opacity: 0.8, metalness: 0.1, roughness: 0.2 });
      mesh = new THREE.Mesh(geometry, material);
      scene.add(mesh);
    }

    window.addEventListener('load', () => {
      resetForm();
      loadItems();
    });
  </script>
</body>
</html>

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
    .shape-options {
      display: inline-flex;
      gap: 16px;
      font-size: 12px;
      color: #374151;
      align-items: center;
      flex-wrap: wrap;
    }
    .shape-options label {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      cursor: pointer;
    }
    .shape-options input {
      accent-color: #2563eb;
    }
    .dim-inline {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
    }
    .dim-inline span.symbol {
      font-size: 13px;
      color: #6b7280;
    }
    .dimension-field input {
      width: 90px;
      text-align: center;
    }
    .input-with-action {
      display: flex;
      align-items: center;
      gap: 6px;
      width: 100%;
    }
    .input-with-action input {
      flex: 1;
    }
    .currency-row {
      display: flex;
      gap: 8px;
      align-items: center;
    }
    .currency-row select {
      min-width: 120px;
    }
    .custom-currency-input {
      flex: 1;
      display: none;
    }

    .micro-btn {
      border-radius: 999px;
      border: 1px solid #d1d5db;
      background: #ffffff;
      color: #1f2937;
      padding: 4px 10px;
      font-size: 11px;
      cursor: pointer;
      transition: background 0.15s, border-color 0.15s;
    }
    .micro-btn:hover {
      background: #f3f4f6;
      border-color: #94a3b8;
    }
    .hint.error {
      color: #b91c1c;
    }
    .edit-indicator {
      background: #fef9c3;
      color: #854d0e;
      padding: 6px 10px;
      border-radius: 10px;
      font-size: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
    }
    .edit-indicator strong {
      font-weight: 600;
    }
    .edit-actions {
      display: flex;
      gap: 6px;
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
    input[type="date"],
    select,
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
    select {
      border-radius: 12px;
    }
    input:focus,
    textarea:focus,
    select:focus {
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
      height: clamp(320px, 60vh, 520px);
      border-radius: 14px;
      border: 1px solid #dbeafe;
      background: linear-gradient(135deg, #eef2ff 0%, #e2e8f0 60%, #f8fafc 100%);
      box-shadow: inset 0 0 30px rgba(15,23,42,0.08);
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
    .log-actions {
      display: flex;
      gap: 6px;
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
    .badge.gray {
      background: #f3f4f6;
      color: #4b5563;
    }

    .tag {
      display: inline-flex;
      align-items: center;
      border-radius: 999px;
      font-size: 11px;
      padding: 1px 8px;
      margin-right: 6px;
    }
    .tag.success {
      background: #dcfce7;
      color: #166534;
    }
    .tag.danger {
      background: #fee2e2;
      color: #991b1b;
    }
    .tag.neutral {
      background: #e0e7ff;
      color: #312e81;
    }

    .history-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      max-height: 240px;
      overflow: auto;
    }
    .history-item {
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 8px 10px;
      font-size: 12px;
      cursor: pointer;
      background: #f9fafb;
      transition: border-color 0.15s, background 0.15s;
    }
    .history-item:hover {
      border-color: #93c5fd;
      background: #eff6ff;
    }
    .history-item.selected {
      border-color: #2563eb;
      box-shadow: 0 0 0 1px rgba(37,99,235,0.2);
      background: #eef2ff;
    }
    .history-title {
      font-weight: 600;
      margin-bottom: 2px;
    }
    .history-meta {
      color: #6b7280;
      font-size: 11px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .blueprint-card {
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 10px;
      margin-top: 10px;
      background: #f9fafb;
    }
    .blueprint-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 12px;
    }
    .bp-label {
      font-size: 11px;
      color: #6b7280;
      margin-bottom: 4px;
    }
    .bp-canvas {
      border: 1px solid #d1d5db;
      border-radius: 0;
      min-height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      background: #ffffff;
      padding: 6px;
    }
    .bp-svg {
      width: 100%;
      height: 150px;
    }
    .bp-empty {
      font-size: 11px;
      color: #9ca3af;
    }
    .bp-dim-text {
      fill: #0f172a;
      font-size: 10px;
      font-weight: 600;
    }
    .bp-guide {
      stroke: #94a3b8;
      stroke-width: 1;
      stroke-dasharray: 4 3;
    }
    .bp-dim-line {
      stroke: #0f172a;
      stroke-width: 1.2;
    }
    .bp-shape-stroke {
      fill: rgba(37,99,235,0.06);
      stroke: #2563eb;
      stroke-width: 1.5;
    }
    .bp-center-line {
      stroke: #cbd5f5;
      stroke-width: 1;
      stroke-dasharray: 6 4;
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
      <span class="sub">長方體填 L×W×H，圓柱填 Ø×H（單位 mm，可輸入小數）</span>
    </div>

    <form id="add-form">
      <div class="inline-fields">
        <div class="field" style="flex:1;">
          <label>廠商 <span class="req">*</span></label>
          <input id="f-vendor" type="text" required placeholder="例如：ABC Diamonds" />
        </div>
        <div class="field" style="flex:1;">
          <label>形狀</label>
          <div class="shape-options">
            <label><input type="radio" name="f-shape" value="box" checked /> 長方體</label>
            <label><input type="radio" name="f-shape" value="cylinder" /> 圓柱</label>
          </div>
        </div>
      </div>

      <div class="field dimension-field" data-shape="box">
        <label>長方體尺寸（L×W×H） <span class="req">*</span></label>
        <div class="dim-inline">
          <input id="f-length" type="number" min="0" step="0.01" placeholder="長 L" />
          <span class="symbol">×</span>
          <input id="f-width" type="number" min="0" step="0.01" placeholder="寬 W" />
          <span class="symbol">×</span>
          <input id="f-height" type="number" min="0" step="0.01" placeholder="高 H" />
        </div>
        <div class="hint">範例：3.5 × 3.5 × 1.0</div>
      </div>

      <div class="field dimension-field" data-shape="cylinder" style="display:none;">
        <label>圓柱尺寸（Ø×H） <span class="req">*</span></label>
        <div class="dim-inline">
          <span class="symbol">Ø</span>
          <input id="f-diameter" type="number" min="0" step="0.01" placeholder="直徑" />
          <span class="symbol">×</span>
          <span class="symbol">H</span>
          <input id="f-height-cylinder" type="number" min="0" step="0.01" placeholder="高度" />
        </div>
        <div class="hint">範例：2.2Ø × 1.0</div>
      </div>

      <div class="inline-fields" style="margin-top:4px;">
        <div class="field">
          <label>數量（塊） <span class="req">*</span></label>
          <input id="f-qty" type="number" min="0" step="1" required value="1" />
        </div>
        <div class="field">
          <label>外幣單價 / 塊</label>
          <input id="f-price-foreign" type="number" min="0" step="0.01" placeholder="例如：30" />
        </div>
      </div>

      <div class="inline-fields">
        <div class="field">
          <label>幣別</label>
<div class="currency-row">
  <!-- 下拉式選單 -->
  <select id="f-currency">
    <option value="CNY" selected>人民幣（CNY）</option>
    <option value="USD">美元（USD）</option>
    <option value="JPY">日圓（JPY）</option>
    <option value="" data-placeholder="true">未指定</option>
    <option value="__custom__">其他（自訂）</option>
  </select>

  <!-- 自訂幣別 -->
  <input
    id="f-currency-custom"
    class="custom-currency-input"
    type="text"
    placeholder="輸入幣別或直接輸入（相容舊版）"
    maxlength="5"
    style="text-transform:uppercase;"
  />
</div>
        </div>
        <div class="field" style="flex:1;">
          <label>匯率（→ TWD）</label>
          <div class="input-with-action">
            <input id="f-exchange-rate" type="number" min="0" step="0.0001" placeholder="例如：31.5" />
            <button type="button" class="micro-btn" id="btn-refresh-rate">重新取得</button>
          </div>
          <div class="hint" id="exchange-hint">預設會依購入日抓取匯率，也可以自行輸入。</div>
        </div>
      </div>

      <div class="field">
        <label>台幣單價 / 塊</label>
        <div class="input-with-action">
          <input id="f-price-twd" type="number" min="0" step="0.01" placeholder="自動換算" />
          <button type="button" class="micro-btn" id="btn-recalc-twd">重新換算</button>
        </div>
      </div>
        </div>
        <div class="hint">系統會用「外幣 × 匯率」預填，也可以覆寫。</div>
      </div>

      <div class="field">
        <label>台幣單價 / 塊</label>
        <div class="input-with-action">
          <input id="f-price-twd" type="number" min="0" step="0.01" placeholder="自動換算" />
          <button type="button" class="micro-btn" id="btn-recalc-twd">重新換算</button>
        </div>
        <div class="hint">系統會用「外幣 × 匯率」預填，也可以覆寫。</div>
      </div>

      <div class="inline-fields">
        <div class="field">
          <label>購買日期</label>
          <input id="f-purchase-date" type="date" />
        </div>
        <div class="field" style="flex:1;">
          <label>材質</label>
          <select id="f-material">
            <option value="">未指定</option>
            <option value="hpht">高溫高壓（HPHT）</option>
            <option value="cvd">CVD</option>
          </select>
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
      <div class="edit-indicator" id="edit-indicator" style="display:none;">
        <span>目前編輯：<strong id="edit-target"></strong></span>
        <div class="edit-actions">
          <button type="button" class="outline" id="btn-cancel-edit">取消</button>
          <button type="button" class="danger" id="btn-delete-current">刪除這批</button>
        </div>
      </div>
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
            <th>購買日</th>
            <th>材質</th>
            <th>單價（外 / 台幣）</th>
            <th>總價（TWD）</th>
            <th>備註</th>
            <th style="text-align:right;">操作</th>
          </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
        <div id="empty-hint" class="empty">尚無任何批次，請先在上方新增一筆。</div>
      </div>
    </div>

    <div class="card" style="margin-top:10px; padding:10px 12px 12px;">
      <div class="card-title">
        <span>歷史批次（已用罄）</span>
        <span class="sub" id="history-summary">0 筆</span>
      </div>
      <div id="history-list" class="history-list" style="margin-top:6px;">
        <div class="empty">尚無用罄批次。</div>
      </div>

      <div class="card-title" style="margin-top:12px;">
        <span>型號耗用統計</span>
        <span class="sub">依尺寸分組，計算平均用罄週期</span>
      </div>
      <div id="model-stats" style="margin-top:6px;">
        <div class="empty">沒有歷史資料。</div>
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

    <div class="blueprint-card">
      <div class="blueprint-grid">
        <div>
          <div class="bp-label">上視圖</div>
          <div class="bp-canvas" id="view-top">
            <div class="bp-empty">尚無數據</div>
          </div>
        </div>
        <div>
          <div class="bp-label">側視圖</div>
          <div class="bp-canvas" id="view-side">
            <div class="bp-empty">尚無數據</div>
          </div>
        </div>
      </div>
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
        <span class="detail-label">單價 / 匯率 / 總價</span>
        <span id="info-price">-</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">備註</span>
        <span id="info-note">-</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">購買日期</span>
        <span id="info-purchase">-</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">材質</span>
        <span id="info-material">-</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">用罄 / 週期</span>
        <span id="info-cycle">-</span>
      </div>
    </div>

    <div class="card" style="margin-top:10px; padding:10px 12px 12px;">
      <div class="card-title">
        <span>領料紀錄</span>
        <span class="badge blue" id="withdraw-scope">
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
  let editingItemId = null;

  let scene = null;
  let camera = null;
  let renderer = null;
  let shapeMesh = null;
  let threeContainer = null;
  const MATERIAL_LABELS = {
    hpht: '高溫高壓（HPHT）',
    cvd: 'CVD'
  };

  // ===== 小工具 =====
  function getSelectedShape() {
    const radio = document.querySelector('input[name="f-shape"]:checked');
    return radio ? radio.value : 'box';
  }

  function updateDimensionFieldsVisibility() {
    const shape = getSelectedShape();
    document.querySelectorAll('.dimension-field').forEach(field => {
      field.style.display = field.dataset.shape === shape ? 'block' : 'none';
    });
  }

  function formatDimensionValue(num) {
    if (!isFinite(num)) return '';
    let str = Number(num).toFixed(3);
    str = str.replace(/0+$/, '');
    str = str.replace(/\.$/, '');
    return str || '0';
  }

  function readDimensionsFromForm() {
    const shape = getSelectedShape();
    if (shape === 'cylinder') {
      const diameter = Number(document.getElementById('f-diameter').value);
      const height = Number(document.getElementById('f-height-cylinder').value);
      if (!isFinite(diameter) || diameter <= 0 || !isFinite(height) || height <= 0) return null;
      return {
        shapeType: 'cylinder',
        length: diameter,
        width: diameter,
        height: height,
        sizeStr: `${formatDimensionValue(diameter)}Øx${formatDimensionValue(height)}`
      };
    } else {
      const length = Number(document.getElementById('f-length').value);
      const width = Number(document.getElementById('f-width').value);
      const height = Number(document.getElementById('f-height').value);
      if (!isFinite(length) || length <= 0 || !isFinite(width) || width <= 0 || !isFinite(height) || height <= 0) return null;
      return {
        shapeType: 'box',
        length,
        width,
        height,
        sizeStr: `${formatDimensionValue(length)}x${formatDimensionValue(width)}x${formatDimensionValue(height)}`
      };
    }
  }

  function parseDateValue(raw) {
    if (!raw) return null;
    let normalized = String(raw).trim();
    if (!normalized) return null;
    if (normalized.length === 10) {
      normalized = normalized + 'T00:00:00';
    } else {
      normalized = normalized.replace(' ', 'T');
    }
    const date = new Date(normalized + 'Z');
    if (Number.isNaN(date.getTime())) return null;
    return date;
  }

  function formatDateTime(raw) {
    const date = parseDateValue(raw);
    if (!date) return raw ? String(raw) : '';
    return date.toLocaleString('zh-TW', { hour12: false });
  }

  function formatShortDate(raw) {
    const date = parseDateValue(raw);
    if (!date) return raw ? String(raw) : '';
    return date.toLocaleDateString('zh-TW');
  }

  function formatMaterial(type) {
    if (!type) return '-';
    const key = String(type).toLowerCase();
    return MATERIAL_LABELS[key] || String(type).toUpperCase();
  }

  function formatMaterialShort(type) {
    if (!type) return '-';
    const key = String(type).toLowerCase();
    if (key === 'hpht') return 'HPHT';
    if (key === 'cvd') return 'CVD';
    return String(type).toUpperCase();
  }

  function computeUsageDays(item) {
    if (!item) return null;
    const start = parseDateValue(item.purchase_date);
    const end = parseDateValue(item.depleted_at);
    if (!start || !end) return null;
    const diff = end.getTime() - start.getTime();
    if (!Number.isFinite(diff) || diff <= 0) return null;
    return diff / (1000 * 60 * 60 * 24);
  }

  function formatUsageText(item) {
    if (!item) return '-';
    const archived = Number(item.is_archived) === 1;
    if (archived) {
      const days = computeUsageDays(item);
      const depletedStr = item.depleted_at ? formatDateTime(item.depleted_at) : '';
      if (days != null && item.purchase_date) {
        return `歷時 ${days.toFixed(1)} 天（${formatShortDate(item.purchase_date)} → ${depletedStr || '-'}）`;
      }
      if (depletedStr) return '用罄於 ' + depletedStr;
      return '已用罄';
    }
    if (item.purchase_date) {
      const start = parseDateValue(item.purchase_date);
      if (start) {
        const diff = Date.now() - start.getTime();
        if (diff > 0) {
          const days = diff / (1000 * 60 * 60 * 24);
          return `已入庫 ${days.toFixed(1)} 天`;
        }
      }
    }
    return '-';
  }

  const PRESET_CURRENCY_CODES = ['CNY', 'USD', 'JPY'];
  const CUSTOM_CURRENCY_VALUE = '__custom__';
  const FALLBACK_RATES = {
    CNY: { rate: 4.45, label: '人民幣' },
    USD: { rate: 32.2, label: '美元' },
    JPY: { rate: 0.23, label: '日圓' }
  };
  function normalizeCurrencyCode(raw) {
    if (!raw) return '';
    return String(raw).trim().toUpperCase().slice(0, 5);
  }

  function getCurrencySelect() {
    return document.getElementById('f-currency');
  }

  function getCustomCurrencyInput() {
    return document.getElementById('f-currency-custom');
  }

  function getCurrentCurrencyCode() {
    const select = getCurrencySelect();
    if (!select) return '';
    if (select.value === CUSTOM_CURRENCY_VALUE) {
      const custom = getCustomCurrencyInput();
      return custom ? normalizeCurrencyCode(custom.value) : '';
    }
    return normalizeCurrencyCode(select.value);
  }

  function setCurrencySelection(value) {
    const select = getCurrencySelect();
    const custom = getCustomCurrencyInput();
    if (!select) return;
    const normalized = normalizeCurrencyCode(value);
    if (!normalized) {
      select.value = '';
      if (custom) {
        custom.value = '';
        custom.style.display = 'none';
      }
      return;
    }
    if (PRESET_CURRENCY_CODES.includes(normalized)) {
      select.value = normalized;
      if (custom) {
        custom.value = '';
        custom.style.display = 'none';
      }
    } else {
      select.value = CUSTOM_CURRENCY_VALUE;
      if (custom) {
        custom.value = normalized;
        custom.style.display = 'inline-block';
      }
    }
  }
  function getTwdUnitPrice(item) {
    if (!item) return null;
    const candidates = [item.unit_price_twd, item.unit_price];
    for (const candidate of candidates) {
      if (candidate != null && isFinite(candidate)) {
        const num = Number(candidate);
        if (Number.isFinite(num)) return num;
      }
    }
    if (item.unit_price_foreign != null && item.exchange_rate != null) {
      const foreign = Number(item.unit_price_foreign);
      const rate = Number(item.exchange_rate);
      if (Number.isFinite(foreign) && Number.isFinite(rate)) {
        return foreign * rate;
      }
    }
    return null;
  }

  function formatPriceForList(item) {
    if (!item) return '-';
    const currency = normalizeCurrencyCode(item.currency_code);
    const foreign = item.unit_price_foreign != null ? Number(item.unit_price_foreign) : null;
    const twd = getTwdUnitPrice(item);
    const parts = [];
    if (foreign != null && Number.isFinite(foreign)) {
      const foreignStr = foreign.toFixed(2);
      parts.push((currency ? currency + ' ' : '') + foreignStr);
    }
    if (twd != null && Number.isFinite(twd)) {
      parts.push('NT$' + twd.toFixed(2));
    }
    return parts.length ? parts.join(' / ') : '-';
  }

  function formatPriceDetail(item) {
    if (!item) return '-';
    const currency = normalizeCurrencyCode(item.currency_code);
    const foreign = item.unit_price_foreign != null ? Number(item.unit_price_foreign) : null;
    const exchangeRate = item.exchange_rate != null ? Number(item.exchange_rate) : null;
    const twd = getTwdUnitPrice(item);
    const qty = Number(item.qty);
    const parts = [];
    if (foreign != null && Number.isFinite(foreign)) {
      parts.push(`${currency ? currency + ' ' : ''}${foreign.toFixed(2)} / 塊`);
    }
    if (exchangeRate != null && Number.isFinite(exchangeRate)) {
      parts.push('匯率 ' + exchangeRate.toFixed(4));
    }
    if (twd != null && Number.isFinite(twd)) {
      parts.push('NT$' + twd.toFixed(2) + ' / 塊');
      if (Number.isFinite(qty)) {
        parts.push('總價 NT$' + (twd * qty).toFixed(2));
      }
    }
    return parts.length ? parts.join(' ｜ ') : '-';
  }

  let rateRequestId = 0;

  function updateExchangeHint(text, isError = false) {
    const hint = document.getElementById('exchange-hint');
    if (!hint) return;
    if (text) {
      hint.textContent = text;
    } else {
      hint.textContent = '預設會依購入日抓取匯率，也可以自行輸入。';
    }
    if (isError) {
      hint.classList.add('error');
    } else {
      hint.classList.remove('error');
    }
  }

  function resetPriceFieldStates() {
    const exchangeInput = document.getElementById('f-exchange-rate');
    if (exchangeInput) delete exchangeInput.dataset.manual;
    const twdInput = document.getElementById('f-price-twd');
    if (twdInput) delete twdInput.dataset.manual;
    updateExchangeHint('預設會依購入日抓取匯率，也可以自行輸入。', false);
  }

  async function fetchExchangeRate(currency, dateStr) {
    const normalized = normalizeCurrencyCode(currency);
    if (!normalized) return null;
    const endpoint = dateStr ? dateStr : 'latest';
    const url = `https://api.exchangerate.host/${endpoint}?base=${encodeURIComponent(normalized)}&symbols=TWD`;
    const res = await fetch(url);
    if (!res.ok) throw new Error('exchange rate api failed');
    const data = await res.json();
    const rate = data && data.rates && data.rates.TWD;
    if (rate == null) return null;
    const num = Number(rate);
    return Number.isFinite(num) ? num : null;
  }

  async function autoFetchExchangeRate(force = false) {
    const rateInput = document.getElementById('f-exchange-rate');
    if (!rateInput) return;

    // 新版：下拉＋自訂欄位
    const select = getCurrencySelect();
    const customInput = getCustomCurrencyInput();

    // 舊版：單一文字輸入欄位
    const legacyCurrencyInput = document.getElementById('f-currency');

    let currency = '';

    if (select || customInput) {
      // 新版 UI：用選單＋自訂邏輯取值
      currency = getCurrentCurrencyCode();
    } else if (legacyCurrencyInput) {
      // 舊版 UI：直接從 input 正規化
      currency = normalizeCurrencyCode(legacyCurrencyInput.value);
      legacyCurrencyInput.value = currency;
    }

    if (!currency) {
      if (force) {
        if (select) {
          if (select.value === CUSTOM_CURRENCY_VALUE) {
            // 新版＋自訂幣別（強制）
            updateExchangeHint('請輸入自訂幣別（例如 EUR）。', true);
          } else {
            // 新版＋尚未選幣別（強制）
            updateExchangeHint('請選擇幣別（例如 CNY）。', true);
          }
        } else {
          // 舊版（強制）
          updateExchangeHint('請輸入幣別（例如 USD）。', true);
        }
      } else {
        if (select) {
          if (select.value === CUSTOM_CURRENCY_VALUE) {
            // 新版＋自訂幣別（非強制）
            updateExchangeHint('請輸入自訂幣別以查詢匯率。');
          } else {
            // 新版＋尚未選幣別（非強制）
            updateExchangeHint('請選擇幣別以查詢匯率。');
          }
        } else {
          // 舊版（非強制）
          updateExchangeHint('請輸入幣別以查詢匯率。');
        }
      }
      rateInput.value = '';
      return;
    }
    if (!force && rateInput.dataset.manual === 'true' && rateInput.value) {
      updateExchangeHint('匯率為手動輸入。');
      return;
    }
    const purchaseDateInput = document.getElementById('f-purchase-date');
    const purchaseDate = purchaseDateInput && purchaseDateInput.value ? purchaseDateInput.value : null;
    const token = ++rateRequestId;
    updateExchangeHint('匯率查詢中…');
    let rate = null;
    let hadError = false;

    try {
      rate = await fetchExchangeRate(currency, purchaseDate);
    } catch (err) {
      // 記錄有錯誤，但先不要急著回傳，後面會決定要不要套 fallback
      hadError = true;
    }

    // 無論成功或失敗，都先檢查 token，避免舊請求覆蓋新結果
    if (token !== rateRequestId) return;

    if (rate != null) {
      // 有成功拿到匯率
      rateInput.value = rate.toFixed(4);
      delete rateInput.dataset.manual;
      updateExchangeHint(
        purchaseDate
          ? `${currency} 對 TWD（${purchaseDate}）`
          : `${currency} 對 TWD 最新匯率`
      );
      const twdInput = document.getElementById('f-price-twd');
      if (twdInput) delete twdInput.dataset.manual;
      updateTwdPriceField({ force: true });
      return;
    }

    // 沒有拿到即時匯率 → 嘗試使用預設 fallback
    const fallback = FALLBACK_RATES[currency];
    if (fallback) {
      rateInput.value = fallback.rate.toFixed(4);
      delete rateInput.dataset.manual;

      const prefix = hadError
        ? '無法連線匯率服務，已套用預設'
        : '查不到這個幣別的即時匯率，已套用預設';

      const displayName = fallback.label ? `${fallback.label}（${currency}）` : currency;

      updateExchangeHint(
        `${prefix} ${displayName} → TWD ${fallback.rate.toFixed(4)}。`
      );

      const twdInput = document.getElementById('f-price-twd');
      if (twdInput) delete twdInput.dataset.manual;
      updateTwdPriceField({ force: true });
    } else {
      // 既沒有拿到即時匯率，也沒有 fallback
      updateExchangeHint(
        hadError
          ? '匯率取得失敗，可手動輸入。'
          : '查不到這個幣別的匯率，請手動填寫。',
        true
      );
    }
    }
  }

  function updateTwdPriceField({ force = false } = {}) {
    const twdInput = document.getElementById('f-price-twd');
    const foreignInput = document.getElementById('f-price-foreign');
    const rateInput = document.getElementById('f-exchange-rate');
    if (!twdInput || !foreignInput || !rateInput) return;
    if (!force && twdInput.dataset.manual === 'true') return;
    if (foreignInput.value === '' || rateInput.value === '') return;
    const foreign = Number(foreignInput.value);
    const rate = Number(rateInput.value);
    if (Number.isFinite(foreign) && Number.isFinite(rate)) {
      const value = foreign * rate;
      if (Number.isFinite(value)) {
        twdInput.value = value.toFixed(2);
      }
    }
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

  async function apiUpdateWithdraw(id, qty, purpose) {
    const res = await fetch('/api/withdraw.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, qty: qty, purpose: purpose })
    });
    if (!res.ok) {
      const txt = await res.text().catch(() => '');
      throw new Error('PUT /api/withdraw.php failed: ' + txt);
    }
    return await res.json();
  }

  async function apiDeleteWithdraw(id) {
    const res = await fetch('/api/withdraw.php?id=' + encodeURIComponent(id), {
      method: 'DELETE'
    });
    if (!res.ok) {
      const txt = await res.text().catch(() => '');
      throw new Error('DELETE /api/withdraw.php failed: ' + txt);
    }
    return await res.json();
  }

  async function refreshItemsFromServer() {
    items = await apiListItems();
    if (editingItemId && !items.find(x => x.id === editingItemId)) {
      editingItemId = null;
      clearForm();
    }
    if (!items.find(x => x.id === selectedId)) {
      const next = items.find(x => Number(x.is_archived) === 0) || items[0] || null;
      selectedId = next ? next.id : null;
    }
    renderList();
    renderHistory();
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

    camera = new THREE.PerspectiveCamera(38, w / h, 0.1, 200);
    camera.position.set(6.4, 6.8, 9.2);
    camera.lookAt(0, 0, 0);

    const ambient = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambient);
    const dir = new THREE.DirectionalLight(0xffffff, 0.85);
    dir.position.set(8, 10, 6);
    scene.add(dir);
    const fill = new THREE.DirectionalLight(0xbcd5ff, 0.4);
    fill.position.set(-6, 4, -4);
    scene.add(fill);
    const hemi = new THREE.HemisphereLight(0xdbeafe, 0x0f172a, 0.3);
    scene.add(hemi);

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

  function computeMmScale(dims) {
    if (!dims) return 0.2;
    const maxDim = Math.max(
      Number(dims.length) || 0,
      Number(dims.width) || 0,
      Number(dims.height) || 0,
      1
    );
    const target = 12; // world units，視角再貼近一些
    const scale = target / maxDim;
    return Math.min(Math.max(scale, 0.25), 3.5);
  }

  function setShapeGeometry(shapeType, dims) {
    if (!scene || !THREE) return;

    if (shapeMesh) {
      scene.remove(shapeMesh);
      shapeMesh.geometry.dispose();
      shapeMesh.material.dispose();
      shapeMesh = null;
    }

    if (!dims) return;

    const mmScale = computeMmScale(dims);
    let geometry = null;

    if (shapeType === 'cylinder') {
      const radius = Math.max((Number(dims.length) / 2) * mmScale, 0.05);
      const height = Math.max(Number(dims.height) * mmScale, 0.05);
      geometry = new THREE.CylinderGeometry(radius, radius, height, 48);
    } else {
      geometry = new THREE.BoxGeometry(
        Math.max(Number(dims.length) * mmScale, 0.05),
        Math.max(Number(dims.height) * mmScale, 0.05),
        Math.max(Number(dims.width) * mmScale, 0.05)
      );
    }

    const mat = new THREE.MeshPhongMaterial({
      color: 0x3b82f6,
      transparent: true,
      opacity: 0.9
    });

    shapeMesh = new THREE.Mesh(geometry, mat);
    shapeMesh.rotation.set(-0.32, 0.55, 0.14);
    scene.add(shapeMesh);
  }

  function animate() {
    requestAnimationFrame(animate);
    if (renderer && scene && camera) {
      renderer.render(scene, camera);
    }
  }

  function updateBlueprintViews(item) {
    renderBlueprintView('view-top', item, 'top');
    renderBlueprintView('view-side', item, 'side');
  }

  function renderBlueprintView(id, item, orientation) {
    const container = document.getElementById(id);
    if (!container) return;
    if (!item || !item.length || !item.width || !item.height) {
      container.innerHTML = '<div class="bp-empty">尚無數據</div>';
      return;
    }

    const dims = {
      length: Number(item.length),
      width: Number(item.width),
      height: Number(item.height)
    };
    if (!isFinite(dims.length) || !isFinite(dims.width) || !isFinite(dims.height)) {
      container.innerHTML = '<div class="bp-empty">尚無數據</div>';
      return;
    }

    const isCylinder = item.shape_type === 'cylinder';
    const targetWidth = dims.length;
    const targetHeight = orientation === 'top'
      ? (isCylinder ? dims.length : dims.width)
      : dims.height;
    const safeWidth = Math.max(targetWidth, 0.1);
    const safeHeight = Math.max(targetHeight, 0.1);
    const canvasWidth = 220;
    const canvasHeight = 150;
    const margin = 32;
    const drawableWidth = canvasWidth - margin * 2;
    const drawableHeight = canvasHeight - margin * 2;
    const scale = Math.min(drawableWidth / safeWidth, drawableHeight / safeHeight);
    const shapeWidth = Math.max(safeWidth * scale, 20);
    const shapeHeight = Math.max(safeHeight * scale, 20);
    const shapeX = (canvasWidth - shapeWidth) / 2;
    const shapeY = (canvasHeight - shapeHeight) / 2;
    const markerId = `${id}-arrow`;

    let shapeElement = '';
    let centerLines = '';
    if (isCylinder && orientation === 'top') {
      const radius = Math.min(shapeWidth, shapeHeight) / 2;
      const cx = shapeX + shapeWidth / 2;
      const cy = shapeY + shapeHeight / 2;
      shapeElement = `<circle class="bp-shape-stroke" cx="${cx}" cy="${cy}" r="${radius}"></circle>`;
      centerLines = `
        <line class="bp-center-line" x1="${cx - radius}" y1="${cy}" x2="${cx + radius}" y2="${cy}"></line>
        <line class="bp-center-line" x1="${cx}" y1="${cy - radius}" x2="${cx}" y2="${cy + radius}"></line>
      `;
    } else {
      shapeElement = `<rect class="bp-shape-stroke" x="${shapeX}" y="${shapeY}" width="${shapeWidth}" height="${shapeHeight}"></rect>`;
      centerLines = `
        <line class="bp-center-line" x1="${shapeX}" y1="${shapeY + shapeHeight / 2}" x2="${shapeX + shapeWidth}" y2="${shapeY + shapeHeight / 2}"></line>
        <line class="bp-center-line" x1="${shapeX + shapeWidth / 2}" y1="${shapeY}" x2="${shapeX + shapeWidth / 2}" y2="${shapeY + shapeHeight}"></line>
      `;
    }

    const horizontalLabel = (isCylinder && orientation === 'side')
      ? `Ø ${formatDimensionValue(dims.length)} mm`
      : `L ${formatDimensionValue(dims.length)} mm`;
    const verticalLabel = orientation === 'top'
      ? (isCylinder ? `Ø ${formatDimensionValue(dims.length)} mm` : `W ${formatDimensionValue(dims.width)} mm`)
      : `H ${formatDimensionValue(dims.height)} mm`;

    const horizontalY = Math.min(shapeY + shapeHeight + 28, canvasHeight - 10);
    const verticalX = Math.max(shapeX - 28, 12);

    const svg = `
      <svg class="bp-svg" viewBox="0 0 ${canvasWidth} ${canvasHeight}" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <marker id="${markerId}" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto" markerUnits="strokeWidth">
            <path d="M0,0 L6,3 L0,6 z" fill="#0f172a"></path>
          </marker>
        </defs>
        ${shapeElement}
        ${centerLines}
        <line class="bp-guide" x1="${shapeX}" y1="${shapeY + shapeHeight}" x2="${shapeX}" y2="${horizontalY}"></line>
        <line class="bp-guide" x1="${shapeX + shapeWidth}" y1="${shapeY + shapeHeight}" x2="${shapeX + shapeWidth}" y2="${horizontalY}"></line>
        <line class="bp-guide" x1="${shapeX}" y1="${shapeY}" x2="${verticalX}" y2="${shapeY}"></line>
        <line class="bp-guide" x1="${shapeX}" y1="${shapeY + shapeHeight}" x2="${verticalX}" y2="${shapeY + shapeHeight}"></line>
        <line class="bp-dim-line" x1="${shapeX}" y1="${horizontalY}" x2="${shapeX + shapeWidth}" y2="${horizontalY}" marker-start="url(#${markerId})" marker-end="url(#${markerId})"></line>
        <line class="bp-dim-line" x1="${verticalX}" y1="${shapeY}" x2="${verticalX}" y2="${shapeY + shapeHeight}" marker-start="url(#${markerId})" marker-end="url(#${markerId})"></line>
        <text class="bp-dim-text" x="${shapeX + shapeWidth / 2}" y="${horizontalY - 4}" text-anchor="middle">${horizontalLabel}</text>
        <text class="bp-dim-text" text-anchor="middle" transform="translate(${verticalX - 6}, ${shapeY + shapeHeight / 2}) rotate(-90)">${verticalLabel}</text>
      </svg>
    `;
    container.innerHTML = svg;
  }

  // ===== UI：列表 =====
  function renderList() {
    const tbody = document.getElementById('tbody');
    const emptyHint = document.getElementById('empty-hint');
    tbody.innerHTML = '';

    const activeItems = (items || []).filter(it => Number(it.is_archived) === 0);

    if (!activeItems || activeItems.length === 0) {
      emptyHint.style.display = 'block';
      document.getElementById('list-summary').textContent = '0 筆（庫存中）';
      return;
    }

    emptyHint.style.display = 'none';
    document.getElementById('list-summary').textContent = activeItems.length + ' 筆（庫存中）';

    activeItems.forEach(it => {
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
      tr.appendChild(td(formatShortDate(it.purchase_date) || '-'));
      tr.appendChild(td(formatMaterialShort(it.material_type)));
      tr.appendChild(td(formatPriceForList(it)));

      let totalStr = '-';
      const twdUnit = getTwdUnitPrice(it);
      if (twdUnit != null && Number.isFinite(twdUnit)) {
        const t = Number(twdUnit) * Number(it.qty);
        if (Number.isFinite(t)) {
          totalStr = t.toFixed(2);
        }
      }
      tr.appendChild(td(totalStr));
      tr.appendChild(td(it.note || ''));

      const tdActions = document.createElement('td');
      tdActions.className = 'actions';
      const btnEdit = document.createElement('button');
      btnEdit.type = 'button';
      btnEdit.textContent = '編輯';
      btnEdit.className = 'outline';
      btnEdit.style.marginRight = '6px';
      btnEdit.dataset.action = 'edit';
      btnEdit.addEventListener('click', e => {
        e.stopPropagation();
        selectedId = it.id;
        loadItemToForm(it);
        renderList();
        renderSelected();
      });
      tdActions.appendChild(btnEdit);

      const btnDel = document.createElement('button');
      btnDel.type = 'button';
      btnDel.textContent = '刪除';
      btnDel.className = 'danger';
      btnDel.dataset.action = 'delete';
      btnDel.addEventListener('click', async e => {
        e.stopPropagation();
        if (!confirm(`確定刪除【${it.vendor}】尺寸 ${it.size_str} 這一批？領料紀錄也會一起刪除。`)) return;
        try {
          await apiDeleteItem(it.id);
          if (selectedId === it.id) selectedId = null;
          if (editingItemId === it.id) {
            editingItemId = null;
            clearForm();
          }
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

  function renderHistory() {
    const historyList = document.getElementById('history-list');
    const historySummary = document.getElementById('history-summary');
    if (!historyList || !historySummary) return;

    const archivedItems = (items || [])
      .filter(it => Number(it.is_archived) === 1)
      .sort((a, b) => {
        const aTime = parseDateValue(a.depleted_at)?.getTime() || 0;
        const bTime = parseDateValue(b.depleted_at)?.getTime() || 0;
        return bTime - aTime;
      });
    historySummary.textContent = archivedItems.length + ' 筆';

    if (archivedItems.length === 0) {
      historyList.innerHTML = '<div class="empty">尚無用罄批次。</div>';
    } else {
      historyList.innerHTML = '';
      archivedItems.forEach(it => {
        const div = document.createElement('div');
        div.className = 'history-item';
        if (it.id === selectedId) {
          div.classList.add('selected');
        }
        const title = document.createElement('div');
        title.className = 'history-title';
        title.textContent = `${it.vendor}｜${it.size_str}`;
        div.appendChild(title);

        const meta1 = document.createElement('div');
        meta1.className = 'history-meta';
        const purchaseText = formatShortDate(it.purchase_date) || '未填';
        const depletedText = it.depleted_at ? formatDateTime(it.depleted_at) : '—';
        meta1.innerHTML = `<span>購買：${purchaseText}</span><span>用罄：${depletedText}</span>`;
        div.appendChild(meta1);

        const meta2 = document.createElement('div');
        meta2.className = 'history-meta';
        meta2.textContent = formatUsageText(it);
        div.appendChild(meta2);

        div.addEventListener('click', () => {
          selectedId = it.id;
          renderSelected();
          renderList();
          renderHistory();
        });

        historyList.appendChild(div);
      });
    }

    renderModelStats(archivedItems);
  }

  function renderModelStats(archivedItems) {
    const container = document.getElementById('model-stats');
    if (!container) return;
    if (!archivedItems || archivedItems.length === 0) {
      container.innerHTML = '<div class="empty">沒有歷史資料。</div>';
      return;
    }

    const grouped = new Map();
    archivedItems.forEach(it => {
      const key = it.size_str || '未填尺寸';
      if (!grouped.has(key)) grouped.set(key, []);
      grouped.get(key).push(it);
    });

    const rows = Array.from(grouped.entries()).map(([size, list]) => {
      const durations = list.map(it => computeUsageDays(it)).filter(v => v != null);
      const avgDays = durations.length ? durations.reduce((sum, cur) => sum + cur, 0) / durations.length : null;
      const latest = list.slice().sort((a, b) => {
        const aTime = parseDateValue(a.depleted_at)?.getTime() || 0;
        const bTime = parseDateValue(b.depleted_at)?.getTime() || 0;
        return bTime - aTime;
      })[0];
      return { size, count: list.length, avgDays, latest };
    }).sort((a, b) => {
      if (b.count !== a.count) return b.count - a.count;
      const avgA = a.avgDays ?? 0;
      const avgB = b.avgDays ?? 0;
      if (avgB !== avgA) return avgB - avgA;
      return a.size.localeCompare(b.size, 'zh-Hant');
    });

    container.innerHTML = '';
    const table = document.createElement('table');
    const thead = document.createElement('thead');
    thead.innerHTML = '<tr><th>尺寸</th><th>批次數</th><th>平均用罄天數</th><th>最近用罄</th></tr>';
    table.appendChild(thead);
    const tbody = document.createElement('tbody');
    rows.forEach(row => {
      const tr = document.createElement('tr');
      tr.style.cursor = 'pointer';
      tr.addEventListener('click', () => {
        if (row.latest) {
          selectedId = row.latest.id;
          renderSelected();
          renderList();
          renderHistory();
        }
      });
      const avgStr = row.avgDays != null ? row.avgDays.toFixed(1) + ' 天' : '—';
      const latestStr = row.latest && row.latest.depleted_at ? formatDateTime(row.latest.depleted_at) : '—';
      tr.innerHTML = `<td>${row.size}</td><td>${row.count}</td><td>${avgStr}</td><td>${latestStr}</td>`;
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    container.appendChild(table);
  }

  // ===== UI：右側詳細＋領料紀錄 =====
  async function renderSelected() {
    const titleEl = document.getElementById('selected-title');
    const dimSpan = document.getElementById('dim-labels').querySelector('span');
    const infoVendor = document.getElementById('info-vendor');
    const infoQty = document.getElementById('info-qty');
    const infoPrice = document.getElementById('info-price');
    const infoNote = document.getElementById('info-note');
    const infoPurchase = document.getElementById('info-purchase');
    const infoMaterial = document.getElementById('info-material');
    const infoCycle = document.getElementById('info-cycle');
    const logList = document.getElementById('log-list');
    const withdrawScope = document.getElementById('withdraw-scope');
    const withdrawForm = document.getElementById('withdraw-form');

    const toggleWithdrawDisabled = disabled => {
      if (!withdrawForm) return;
      withdrawForm.querySelectorAll('input, button').forEach(el => {
        if (el.tagName === 'INPUT' || el.type === 'submit') {
          el.disabled = disabled;
        }
      });
    };

    const it = items.find(x => x.id === selectedId);
    if (!it) {
      titleEl.textContent = '尚未選擇批次';
      dimSpan.textContent = '尺寸：-';
      infoVendor.textContent = '-';
      infoQty.textContent = '-';
      infoPrice.textContent = '-';
      infoNote.textContent = '-';
      infoPurchase.textContent = '-';
      infoMaterial.textContent = '-';
      infoCycle.textContent = '-';
      logList.innerHTML = '<div class="empty">尚無領料紀錄。</div>';
      updateBlueprintViews(null);
      if (withdrawScope) {
        withdrawScope.className = 'badge gray';
        withdrawScope.innerHTML = '<span style="width:6px;height:6px;border-radius:999px;background:#9ca3af;"></span>請先選擇批次';
      }
      toggleWithdrawDisabled(true);
      if (shapeMesh && scene) {
        scene.remove(shapeMesh);
        shapeMesh.geometry.dispose();
        shapeMesh.material.dispose();
        shapeMesh = null;
      }
      return;
    }

    const archived = Number(it.is_archived) === 1;
    const statusTag = archived ? '<span class="tag danger">已用罄</span>' : '<span class="tag success">庫存中</span>';
    titleEl.innerHTML = statusTag + ' ' + it.vendor + '｜' + it.size_str;
    const dimLabel = (it.shape_type === 'cylinder') ? 'Ø×H' : 'L×W×H';
    dimSpan.innerHTML = dimLabel + '：<code>' + it.size_str + '</code>';
    infoVendor.textContent = it.vendor;
    infoQty.textContent = it.qty + ' 塊' + (archived ? '（已用罄）' : '');

    infoPrice.textContent = formatPriceDetail(it);
    infoNote.textContent = it.note || '-';
    infoPurchase.textContent = formatShortDate(it.purchase_date) || '-';
    infoMaterial.textContent = formatMaterial(it.material_type);
    infoCycle.textContent = formatUsageText(it);

    updateBlueprintViews(it);

    if (it.length && it.width && it.height) {
      setShapeGeometry(it.shape_type || 'box', {
        length: Number(it.length),
        width: Number(it.width),
        height: Number(it.height)
      });
    } else if (shapeMesh && scene) {
      scene.remove(shapeMesh);
      shapeMesh.geometry.dispose();
      shapeMesh.material.dispose();
      shapeMesh = null;
    }

    if (withdrawScope) {
      withdrawScope.className = 'badge ' + (archived ? 'gray' : 'blue');
      const dotColor = archived ? '#9ca3af' : '#1d4ed8';
      const scopeText = archived ? '已用罄，僅供查詢' : '針對目前選取批次';
      withdrawScope.innerHTML = `<span style="width:6px;height:6px;border-radius:999px;background:${dotColor};"></span>${scopeText}`;
    }
    toggleWithdrawDisabled(archived);

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

          const header = document.createElement('div');
          header.style.display = 'flex';
          header.style.justifyContent = 'space-between';
          header.style.alignItems = 'center';
          const timeSpan = document.createElement('span');
          timeSpan.className = 'time';
          timeSpan.textContent = timeStr;
          header.appendChild(timeSpan);

          const actions = document.createElement('div');
          actions.className = 'log-actions';

          const btnEdit = document.createElement('button');
          btnEdit.type = 'button';
          btnEdit.className = 'outline';
          btnEdit.style.fontSize = '10px';
          btnEdit.style.padding = '2px 8px';
          btnEdit.style.borderRadius = '999px';
          btnEdit.textContent = '編輯';
          btnEdit.addEventListener('click', async () => {
            const qtyStr = window.prompt('修改領出數量：', log.qty);
            if (qtyStr === null) return;
            const newQty = Number(qtyStr);
            if (!Number.isInteger(newQty) || newQty <= 0) {
              alert('領出數量必須是正整數。');
              return;
            }
            const newPurpose = window.prompt('修改用途說明（可留空）：', log.purpose || '');
            if (newPurpose === null) return;
            try {
              await apiUpdateWithdraw(log.id, newQty, newPurpose.trim());
              await refreshItemsFromServer();
            } catch (e2) {
              alert('更新失敗：' + e2.message);
            }
          });

          const btnDel = document.createElement('button');
          btnDel.type = 'button';
          btnDel.className = 'danger';
          btnDel.style.fontSize = '10px';
          btnDel.style.padding = '2px 8px';
          btnDel.style.borderRadius = '999px';
          btnDel.textContent = '撤回';
          btnDel.addEventListener('click', async () => {
            if (!confirm(`確定撤回 ${log.qty} 塊的領料紀錄？`)) return;
            try {
              await apiDeleteWithdraw(log.id);
              await refreshItemsFromServer();
            } catch (e2) {
              alert('撤回失敗：' + e2.message);
            }
          });

          actions.appendChild(btnEdit);
          actions.appendChild(btnDel);
          header.appendChild(actions);
          div.appendChild(header);

          const body = document.createElement('div');
          const qtySpan = document.createElement('span');
          qtySpan.className = 'qty';
          qtySpan.textContent = `領出 ${log.qty} 塊`;
          const purposeSpan = document.createElement('span');
          purposeSpan.className = 'purpose';
          purposeSpan.textContent = '｜' + purposeText;
          body.appendChild(qtySpan);
          body.appendChild(purposeSpan);
          div.appendChild(body);

          logList.appendChild(div);
        });
      }
    } catch (e) {
      logList.innerHTML = '<div class="empty" style="color:#b91c1c;">讀取領料紀錄失敗：' + e.message + '</div>';
    }
  }

  // ===== 表單處理 =====
  function clearForm() {
    document.getElementById('f-vendor').value = '';
    document.getElementById('f-length').value = '';
    document.getElementById('f-width').value = '';
    document.getElementById('f-height').value = '';
    document.getElementById('f-diameter').value = '';
    document.getElementById('f-height-cylinder').value = '';
    document.getElementById('f-qty').value = '1';
    document.getElementById('f-price-foreign').value = '';
    setCurrencySelection('CNY');
    document.getElementById('f-exchange-rate').value = '';
    document.getElementById('f-price-twd').value = '';
    document.getElementById('f-note').value = '';
    document.getElementById('f-purchase-date').value = '';
    document.getElementById('f-material').value = '';
    resetPriceFieldStates();
    const err = document.getElementById('add-error');
    err.style.display = 'none';
    err.textContent = '';
    const indicator = document.getElementById('edit-indicator');
    indicator.style.display = 'none';
    document.getElementById('edit-target').textContent = '';
    document.getElementById('btn-add').textContent = '儲存批次';
    const defaultShape = document.querySelector('input[name="f-shape"][value="box"]');
    if (defaultShape) defaultShape.checked = true;
    updateDimensionFieldsVisibility();
    editingItemId = null;
    autoFetchExchangeRate();
  }

  function loadItemToForm(item) {
    if (!item) return;
    document.getElementById('f-vendor').value = item.vendor || '';
    document.getElementById('f-qty').value = Number(item.qty);
    document.getElementById('f-price-foreign').value = item.unit_price_foreign != null ? item.unit_price_foreign : '';
    // 依照目前 UI 型態，決定怎麼填回幣別
    if (getCurrencySelect() || getCustomCurrencyInput()) {
      // 新版 UI：下拉 + 自訂幣別
      setCurrencySelection(item.currency_code);
    } else {
      // 舊版 UI：單一文字輸入
      const currencyInput = document.getElementById('f-currency');
      if (currencyInput) {
        currencyInput.value = normalizeCurrencyCode(item.currency_code);
      }
    }
    document.getElementById('f-exchange-rate').value = item.exchange_rate != null ? item.exchange_rate : '';
    const twdValue = item.unit_price_twd != null ? item.unit_price_twd : (item.unit_price != null ? item.unit_price : '');
    document.getElementById('f-price-twd').value = twdValue;
    document.getElementById('f-note').value = item.note || '';
    document.getElementById('f-purchase-date').value = item.purchase_date || '';
    document.getElementById('f-material').value = item.material_type || '';
    resetPriceFieldStates();

    const shape = item.shape_type === 'cylinder' ? 'cylinder' : 'box';
    document.querySelectorAll('input[name="f-shape"]').forEach(radio => {
      radio.checked = radio.value === shape;
    });
    updateDimensionFieldsVisibility();

    if (shape === 'cylinder') {
      document.getElementById('f-diameter').value = item.length || '';
      document.getElementById('f-height-cylinder').value = item.height || '';
      document.getElementById('f-length').value = '';
      document.getElementById('f-width').value = '';
      document.getElementById('f-height').value = '';
    } else {
      document.getElementById('f-length').value = item.length || '';
      document.getElementById('f-width').value = item.width || '';
      document.getElementById('f-height').value = item.height || '';
      document.getElementById('f-diameter').value = '';
      document.getElementById('f-height-cylinder').value = '';
    }

    editingItemId = item.id;
    const indicator = document.getElementById('edit-indicator');
    indicator.style.display = 'flex';
    document.getElementById('edit-target').textContent = `${item.vendor}｜${item.size_str}`;
    document.getElementById('btn-add').textContent = '更新批次';
  }

  async function handleSaveItem(e) {
    e.preventDefault();
    const err = document.getElementById('add-error');
    err.style.display = 'none';
    err.textContent = '';

    const vendor = document.getElementById('f-vendor').value.trim();
    const qty = Number(document.getElementById('f-qty').value);
    const foreignPriceStr = document.getElementById('f-price-foreign').value;
    const exchangeRateStr = document.getElementById('f-exchange-rate').value;
    const priceTwdStr = document.getElementById('f-price-twd').value;
    const note = document.getElementById('f-note').value.trim();
    const purchaseDate = document.getElementById('f-purchase-date').value;
    const materialType = document.getElementById('f-material').value;

    if (!vendor) {
      err.textContent = '廠商必填。';
      err.style.display = 'block';
      return;
    }
    const dimensions = readDimensionsFromForm();
    if (!dimensions) {
      err.textContent = '請輸入正確的尺寸。';
      err.style.display = 'block';
      return;
    }
    if (!Number.isInteger(qty) || qty < 0) {
      err.textContent = '數量必須是 0 或以上的整數。';
      err.style.display = 'block';
      return;
    }

    const currencySelect = getCurrencySelect();
    let currencyCode = '';

    if (currencySelect || getCustomCurrencyInput()) {
      // 新版 UI：下拉 + 自訂幣別
      currencyCode = getCurrentCurrencyCode();

      // 若選的是「自訂幣別」但沒輸入內容 → 報錯
      if (currencySelect && currencySelect.value === CUSTOM_CURRENCY_VALUE && !currencyCode) {
        err.textContent = '請輸入自訂幣別（例如 EUR）。';
        err.style.display = 'block';
        return;
      }
    } else {
      // 舊版 UI：只有單一文字輸入欄位 #f-currency
      const legacyCurrencyInput = document.getElementById('f-currency');
      currencyCode = legacyCurrencyInput ? normalizeCurrencyCode(legacyCurrencyInput.value) : '';
      if (legacyCurrencyInput) {
        // 正規化後寫回去
        legacyCurrencyInput.value = currencyCode;
      }
    }
    if (currencyCode && !/^[A-Z]{2,5}$/.test(currencyCode)) {
      err.textContent = '幣別請輸入 2~5 個英文字母（例如 USD）。';
      err.style.display = 'block';
      return;
    }

    let unitPriceForeign = null;
    if (foreignPriceStr) {
      const value = Number(foreignPriceStr);
      if (!isFinite(value) || value < 0) {
        err.textContent = '外幣單價格式錯誤。';
        err.style.display = 'block';
        return;
      }
      unitPriceForeign = value;
    }

    let exchangeRate = null;
    if (exchangeRateStr) {
      const value = Number(exchangeRateStr);
      if (!isFinite(value) || value <= 0) {
        err.textContent = '匯率必須大於 0。';
        err.style.display = 'block';
        return;
      }
      exchangeRate = value;
    }

    let unitPriceTwd = null;
    if (priceTwdStr) {
      const value = Number(priceTwdStr);
      if (!isFinite(value) || value < 0) {
        err.textContent = '台幣單價格式錯誤。';
        err.style.display = 'block';
        return;
      }
      unitPriceTwd = value;
    } else if (unitPriceForeign != null && exchangeRate != null) {
      unitPriceTwd = Number(unitPriceForeign) * Number(exchangeRate);
    }

    const payload = {
      vendor: vendor,
      size_str: dimensions.sizeStr,
      length: dimensions.length,
      width: dimensions.width,
      height: dimensions.height,
      shape_type: dimensions.shapeType,
      qty: qty,
      unit_price: unitPriceTwd,
      unit_price_twd: unitPriceTwd,
      unit_price_foreign: unitPriceForeign,
      currency_code: currencyCode || null,
      exchange_rate: exchangeRate,
      note: note
    };
    if (purchaseDate) {
      payload.purchase_date = purchaseDate;
    }
    if (materialType) {
      payload.material_type = materialType;
    }
    if (editingItemId) {
      payload.id = editingItemId;
    }

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

  async function handleDeleteCurrentItem() {
    if (!editingItemId) return;
    const current = items.find(x => x.id === editingItemId);
    const label = current ? `【${current.vendor}】尺寸 ${current.size_str}` : '這一批';
    if (!confirm(`確定刪除 ${label}？領料紀錄也會一併刪除。`)) return;
    try {
      await apiDeleteItem(editingItemId);
      if (selectedId === editingItemId) selectedId = null;
      editingItemId = null;
      clearForm();
      await refreshItemsFromServer();
    } catch (e) {
      alert('刪除失敗：' + e.message);
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
    if (Number(it.is_archived) === 1) {
      err.textContent = '此批次已用罄，只能查閱歷史紀錄。';
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
    document.getElementById('btn-cancel-edit').addEventListener('click', clearForm);
    document.getElementById('btn-delete-current').addEventListener('click', handleDeleteCurrentItem);
    document.getElementById('withdraw-form').addEventListener('submit', handleWithdraw);
    document.querySelectorAll('input[name="f-shape"]').forEach(radio => {
      radio.addEventListener('change', updateDimensionFieldsVisibility);
    });
    updateDimensionFieldsVisibility();

    const priceForeignInput = document.getElementById('f-price-foreign');
    if (priceForeignInput) {
      priceForeignInput.addEventListener('input', () => updateTwdPriceField());
    }
    const currencySelectEl = getCurrencySelect();
    const currencyCustomInput = getCustomCurrencyInput();

    if (currencySelectEl) {
      // 新版：下拉 + 自訂幣別
      currencySelectEl.addEventListener('change', () => {
        if (currencySelectEl.value === CUSTOM_CURRENCY_VALUE) {
          if (currencyCustomInput) {
            currencyCustomInput.style.display = 'inline-block';
            currencyCustomInput.focus();
          }
        } else if (currencyCustomInput) {
          currencyCustomInput.style.display = 'none';
          currencyCustomInput.value = '';
        }
        autoFetchExchangeRate();
      });

      if (currencyCustomInput) {
        const handleCustomCurrencyChange = () => {
          currencyCustomInput.value = normalizeCurrencyCode(currencyCustomInput.value);
          autoFetchExchangeRate();
        };
        currencyCustomInput.addEventListener('change', handleCustomCurrencyChange);
        currencyCustomInput.addEventListener('blur', handleCustomCurrencyChange);
      }

      // 新版預設 CNY，直接抓匯率
      setCurrencySelection('CNY');
      autoFetchExchangeRate();
    } else {
      // 舊版：只有一個文字輸入框 #f-currency
      const currencyInput = document.getElementById('f-currency');
      if (currencyInput) {
        const handleCurrencyChange = () => {
          currencyInput.value = normalizeCurrencyCode(currencyInput.value);
          if (currencyInput.value) {
            autoFetchExchangeRate();
          } else {
            updateExchangeHint('請輸入幣別以查詢匯率。');
          }
        };
        currencyInput.addEventListener('change', handleCurrencyChange);
        currencyInput.addEventListener('blur', handleCurrencyChange);
      }
    }
    const exchangeInput = document.getElementById('f-exchange-rate');
    if (exchangeInput) {
      exchangeInput.addEventListener('input', () => {
        exchangeInput.dataset.manual = 'true';
        updateTwdPriceField({ force: true });
      });
    }
    const purchaseDateInput = document.getElementById('f-purchase-date');
    if (purchaseDateInput) {
      purchaseDateInput.addEventListener('change', () => autoFetchExchangeRate());
    }
    const twdInput = document.getElementById('f-price-twd');
    if (twdInput) {
      twdInput.addEventListener('input', () => {
        twdInput.dataset.manual = 'true';
      });
    }
    const refreshRateBtn = document.getElementById('btn-refresh-rate');
    if (refreshRateBtn) {
      refreshRateBtn.addEventListener('click', e => {
        e.preventDefault();
        autoFetchExchangeRate(true);
      });
    }
    const recalcBtn = document.getElementById('btn-recalc-twd');
    if (recalcBtn) {
      recalcBtn.addEventListener('click', e => {
        e.preventDefault();
        const twdField = document.getElementById('f-price-twd');
        if (twdField) delete twdField.dataset.manual;
        updateTwdPriceField({ force: true });
      });
    }

    try {
      await refreshItemsFromServer();
    } catch (e) {
      alert('載入資料失敗：' + e.message);
    }
  });
</script>
</body>
</html>

var{escapeHtml:e}=window.App,t={text:t=>e(t??`—`),badge:t=>t?`<span class="badge badge-pill badge-${e(t.color)}">${e(t.text)}</span>`:``,badges:t=>(t??[]).map(t=>`<span class="badge badge-pill badge-gray me-1">${e(t)}</span>`).join(``)||`<span class="text-muted">—</span>`,user:t=>`
        <div class="d-flex align-items-center gap-2">
            <span class="avatar">${e(t.initials)}</span>
            <div class="lh-sm">
                <div class="fw-semibold text-dark">${e(t.name)}</div>
                <small class="text-muted">${e(t.email)}</small>
            </div>
        </div>`,actions:t=>{if(!t)return``;let n=[];return t.view&&n.push(`<li><button type="button" class="dropdown-item" data-view-url="${e(t.view)}"><i class="bi bi-eye text-primary me-2"></i> View</button></li>`),t.edit&&n.push(`<li><a href="${e(t.edit)}" class="dropdown-item"><i class="bi bi-pencil text-warning me-2"></i> Edit</a></li>`),t.delete&&(n.length&&n.push(`<li><hr class="dropdown-divider"></li>`),n.push(`
                <li>
                    <form method="POST" action="${e(t.delete)}" class="d-inline" data-confirm="${e(t.label||`Record`)} will be permanently deleted.">
                        <input type="hidden" name="_token" value="${e(window.App.csrfToken)}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash text-danger me-2"></i> Delete</button>
                    </form>
                </li>`)),`<div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill me-1"></i> Action
            </button>
            <ul class="dropdown-menu shadow-sm">
                ${n.join(``)}
            </ul>
        </div>`}};function n(e){let t=window.jQuery,n=e.table().node(),r=[],i=[];t(n).find(`thead tr th`).each(function(e){let n=t(this),a=n.text().trim(),o=a.toLowerCase();!n.hasClass(`no-export`)&&o!==`action`&&o!==`actions`&&(r.push(a),i.push(e))});let a=[];return t(n).find(`tbody tr`).each(function(){let e=t(this);if(e.hasClass(`dataTables_empty`)||e.find(`.spinner-border`).length)return;let n=[];e.find(`td`).each(function(e){if(i.includes(e)){let e=t(this).text().trim().replace(/\s+/g,` `);n.push(e)}}),n.length&&a.push(n)}),{headers:r,rows:a,title:(document.title||`Table_Export`).replace(/\s*—.*$/,``).trim()}}function r(e){let{headers:t,rows:r,title:i}=n(e);if(!r.length){window.toastr&&window.toastr.info(`No table data to export`);return}let a=e=>`"${(e||``).replace(/"/g,`""`)}"`,o=[t.map(a).join(`,`),...r.map(e=>e.map(a).join(`,`))].join(`\r
`),s=new Blob([`﻿`+o],{type:`text/csv;charset=utf-8;`}),c=document.createElement(`a`);c.href=URL.createObjectURL(s),c.download=`${i.toLowerCase().replace(/[^a-z0-9]+/g,`_`)}_${new Date().toISOString().slice(0,10)}.csv`,document.body.appendChild(c),c.click(),document.body.removeChild(c),URL.revokeObjectURL(c.href),window.toastr&&window.toastr.success(`CSV export generated successfully`)}function i(e){let{headers:t,rows:r,title:i}=n(e);if(!r.length){window.toastr&&window.toastr.info(`No table data to export`);return}let a=e=>(e||``).replace(/&/g,`&amp;`).replace(/</g,`&lt;`).replace(/>/g,`&gt;`).replace(/"/g,`&quot;`),o=`<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Worksheet ss:Name="Records">
  <Table>
   <Row>${t.map(e=>`<Cell><Data ss:Type="String">${a(e)}</Data></Cell>`).join(``)}</Row>
   ${r.map(e=>`<Row>${e.map(e=>`<Cell><Data ss:Type="String">${a(e)}</Data></Cell>`).join(``)}</Row>`).join(`
   `)}
  </Table>
 </Worksheet>
</Workbook>`,s=new Blob([o],{type:`application/vnd.ms-excel`}),c=document.createElement(`a`);c.href=URL.createObjectURL(s),c.download=`${i.toLowerCase().replace(/[^a-z0-9]+/g,`_`)}_${new Date().toISOString().slice(0,10)}.xls`,document.body.appendChild(c),c.click(),document.body.removeChild(c),URL.revokeObjectURL(c.href),window.toastr&&window.toastr.success(`Excel export generated successfully`)}function a(e){let{headers:t,rows:r,title:i}=n(e);if(!r.length){window.toastr&&window.toastr.info(`No table data to print`);return}let a=window.open(``,`_blank`);if(!a){window.print();return}let o=`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>${i}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; padding: 24px; color: #232333; }
        h2 { font-size: 18px; margin-bottom: 4px; color: #696cff; font-weight: 700; }
        .meta { font-size: 11px; color: #6c757d; margin-bottom: 16px; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #dee2e6; padding: 6px 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; text-transform: uppercase; font-size: 10px; color: #566a7f; }
        tr:nth-child(even) { background: #fafbfc; }
        @media print {
            body { padding: 0; }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body>
    <h2>${i}</h2>
    <div class="meta">Printed on ${new Date().toLocaleString()} &bull; Total Rows: ${r.length}</div>
    <table>
        <thead><tr>${t.map(e=>`<th>${e}</th>`).join(``)}</tr></thead>
        <tbody>${r.map(e=>`<tr>${e.map(e=>`<td>${e}</td>`).join(``)}</tr>`).join(``)}</tbody>
    </table>
</body>
</html>`;a.document.open(),a.document.write(o),a.document.close(),a.focus(),setTimeout(()=>{a.print(),a.close()},300)}function o(e){let{headers:t,rows:r}=n(e);if(!r.length){window.toastr&&window.toastr.info(`No table data to copy`);return}let i=[t.join(`	`),...r.map(e=>e.join(`	`))].join(`
`);if(navigator.clipboard&&navigator.clipboard.writeText)navigator.clipboard.writeText(i).then(()=>{window.toastr&&window.toastr.success(`Table records copied to clipboard`)});else{let e=document.createElement(`textarea`);e.value=i,document.body.appendChild(e),e.select(),document.execCommand(`copy`),document.body.removeChild(e),window.toastr&&window.toastr.success(`Table records copied to clipboard`)}}function s(e){if(!window.jQuery||!window.jQuery.fn||!window.jQuery.fn.dataTable)return;let t=window.jQuery,n;try{n=t(e).DataTable()}catch{return}if(!n||!n.table)return;let s=t(n.table().container());if(!s.length||s.find(`.sneat-dt-export-group`).length)return;let c=t(`
        <div class="sneat-dt-export-group btn-group ms-2">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1.5 py-1.5 px-3 fw-medium shadow-2xs" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download text-primary me-1"></i>
                <span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg py-2 border" style="min-width: 185px; z-index: 1060;">
                <li class="dropdown-header small text-uppercase fw-bold text-muted px-3 py-1">Export Table Data</li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-print"><i class="bi bi-printer text-primary fs-6"></i><span>Print Table</span></button></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-excel"><i class="bi bi-file-earmark-excel text-success fs-6"></i><span>Export Excel (.xls)</span></button></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-csv"><i class="bi bi-filetype-csv text-info fs-6"></i><span>Export CSV</span></button></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-copy"><i class="bi bi-clipboard text-secondary fs-6"></i><span>Copy to Clipboard</span></button></li>
            </ul>
        </div>
    `);c.find(`.dt-trigger-print`).on(`click`,e=>{e.preventDefault(),a(n)}),c.find(`.dt-trigger-excel`).on(`click`,e=>{e.preventDefault(),i(n)}),c.find(`.dt-trigger-csv`).on(`click`,e=>{e.preventDefault(),r(n)}),c.find(`.dt-trigger-copy`).on(`click`,e=>{e.preventDefault(),o(n)});let l=s.find(`.dataTables_filter`);if(l.length)l.addClass(`d-flex align-items-center justify-content-end`),c.appendTo(l);else{let e=s.find(`.row:first-child`);if(e.length){let n=e.find(`> div:last-child`);n.length?(n.addClass(`d-flex align-items-center justify-content-end`),c.appendTo(n)):e.append(t(`<div class="col-12 d-flex justify-content-end p-2"></div>`).append(c))}else{let e=t(`<div class="d-flex justify-content-end p-2 border-bottom dt-export-bar"></div>`).append(c);s.prepend(e)}}}function c(){if(!window.jQuery||!window.jQuery.fn||!window.jQuery.fn.dataTable)return;let e=window.jQuery;e(`table.dataTable, table[id$="-table"]`).each(function(){e.fn.DataTable.isDataTable(this)&&s(this)})}if(window.jQuery&&window.jQuery.fn&&window.jQuery.fn.dataTable){let e=window.jQuery,t=Number(document.querySelector(`meta[name="app-default-page-length"]`)?.content||25);e.extend(!0,e.fn.dataTable.defaults,{pageLength:t,lengthMenu:[10,25,50,100],autoWidth:!1}),e(document).on(`init.dt`,function(e,t){s(t.nTable)}),e(document).on(`draw.dt`,function(e,t){s(t.nTable)})}c(),window.jQuery&&window.jQuery(document).ready(c),document.addEventListener(`DOMContentLoaded`,c),window.addEventListener(`load`,c),setTimeout(c,250),setTimeout(c,800),setTimeout(c,2e3),window.attachDataTablesExport=s,window.App&&(window.App.attachDataTablesExport=s);function l(e){let n=Number(document.querySelector(`meta[name="app-default-page-length"]`)?.content||25),r=e.dataset.url,i=e.dataset.filters?document.getElementById(e.dataset.filters):null,a={pageLength:Number(e.dataset.pageLength||n),lengthMenu:[10,25,50,100],autoWidth:!1,language:{search:``,searchPlaceholder:`Search...`,emptyTable:`No records found`,processing:`<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading…`}};if(r){let n=JSON.parse(e.dataset.columns||`[]`);Object.assign(a,{serverSide:!0,processing:!0,searchDelay:400,order:[],ajax:{url:r,headers:{"X-Requested-With":`XMLHttpRequest`},data:e=>(i&&new FormData(i).forEach((t,n)=>{e[n]=t}),e)},columns:n.map(e=>({data:e.data,title:e.title,orderable:e.orderable??!1,searchable:!1,className:e.className??``,render:(n,r,i)=>(t[e.renderType??e.type??`text`]??t.text)(n,i)}))})}else a.order=[],a.columnDefs=[{targets:`no-sort`,orderable:!1}];let o=new window.DataTable(e,a);if(s(e),i){let e=()=>o.ajax.reload();i.addEventListener(`change`,e),i.addEventListener(`submit`,t=>{t.preventDefault(),e()}),i.addEventListener(`reset`,()=>setTimeout(e))}return o}document.addEventListener(`DOMContentLoaded`,()=>{document.querySelectorAll(`table[data-datatable]`).forEach(l)});
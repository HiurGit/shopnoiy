/* Shared interactive features for backend static pages */
(function () {
  "use strict";

  function normalize(text) {
    return (text || "").toString().toLowerCase().trim();
  }

  function getMainTable() {
    return document.querySelector(".card .table, .table");
  }

  function filterRows() {
    var table = getMainTable();
    if (!table || !table.tBodies || !table.tBodies.length) return;

    var bodyRows = Array.from(table.tBodies[0].rows);
    var searchInput = document.querySelector(".toolbar-row input.form-control");
    var selects = Array.from(document.querySelectorAll(".toolbar-row select.form-select"));

    var query = normalize(searchInput ? searchInput.value : "");
    var selectedValues = selects
      .map(function (s) {
        return normalize(s.value);
      })
      .filter(Boolean);

    bodyRows.forEach(function (row) {
      var rowText = normalize(row.innerText);
      var matchesQuery = !query || rowText.indexOf(query) !== -1;
      var matchesSelects = selectedValues.every(function (val) {
        if (val.indexOf("tat ca") === 0) return true;
        return rowText.indexOf(val) !== -1;
      });
      row.style.display = matchesQuery && matchesSelects ? "" : "none";
    });
  }

  function tableToCsv(table) {
    var lines = [];
    Array.from(table.querySelectorAll("tr")).forEach(function (tr) {
      var cols = Array.from(tr.querySelectorAll("th,td")).map(function (cell) {
        var text = (cell.innerText || "").replace(/\s+/g, " ").trim();
        text = text.replace(/"/g, '""');
        return '"' + text + '"';
      });
      if (cols.length) lines.push(cols.join(","));
    });
    return "\uFEFF" + lines.join("\n");
  }

  function downloadCsv(filename, content) {
    var blob = new Blob([content], { type: "text/csv;charset=utf-8;" });
    var url = URL.createObjectURL(blob);
    var link = document.createElement("a");
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }

  function bindExportButtons() {
    var table = getMainTable();
    if (!table) return;

    var buttons = Array.from(document.querySelectorAll("button, a.btn"));
    buttons.forEach(function (btn) {
      var label = normalize(btn.innerText);
      if (!label) return;
      var isExport =
        label.indexOf("xuat excel") !== -1 ||
        label.indexOf("xuat danh sach") !== -1 ||
        label.indexOf("export") !== -1;

      if (!isExport) return;
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        var title = document.title
          .toLowerCase()
          .replace(/\s+/g, "-")
          .replace(/[^a-z0-9\-]/g, "");
        var csv = tableToCsv(table);
        downloadCsv((title || "backend-table") + ".csv", csv);
      });
    });
  }

  function bindToolbarFilters() {
    var searchInput = document.querySelector(".toolbar-row input.form-control");
    var selects = Array.from(document.querySelectorAll(".toolbar-row select.form-select"));
    if (!searchInput && !selects.length) return;

    if (searchInput) searchInput.addEventListener("input", filterRows);
    selects.forEach(function (s) {
      s.addEventListener("change", filterRows);
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    bindToolbarFilters();
    bindExportButtons();
  });
})();


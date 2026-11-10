// Tìm kiếm trực tiếp trên trang danh sách sách: gõ tới đâu cập nhật kết quả tới đó
document.addEventListener('DOMContentLoaded', function () {
  var input = document.querySelector('.search input[name="q"]');
  var shop = document.querySelector('.shop');
  if (!input || !shop) return; // chỉ chạy ở trang danh sách sách

  var timer = null;
  var controller = null;

  function load() {
    var params = new URLSearchParams(window.location.search);
    var q = input.value.trim();
    if (q) { params.set('q', q); } else { params.delete('q'); }
    params.delete('page'); // từ khóa mới thì quay về trang 1
    var url = window.location.pathname + (params.toString() ? '?' + params : '');

    if (controller) controller.abort(); // hủy yêu cầu cũ nếu còn đang chạy
    controller = new AbortController();
    shop.style.opacity = '.55';

    fetch(url, { signal: controller.signal })
      .then(function (res) { return res.text(); })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var fresh = doc.querySelector('.shop');
        if (fresh) {
          shop.innerHTML = fresh.innerHTML;
          history.replaceState(null, '', url); // địa chỉ trên thanh URL cập nhật theo
        }
        shop.style.opacity = '';
      })
      .catch(function (err) {
        if (err.name !== 'AbortError') shop.style.opacity = '';
      });
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(load, 150); // đợi 150ms sau lần gõ cuối cùng
  });

  // Bấm Enter cũng không tải lại trang
  input.form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearTimeout(timer);
    load();
  });
});
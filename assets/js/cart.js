// Giỏ hàng: cập nhật số lượng và thêm sách vào giỏ mà không cần tải lại trang
document.addEventListener('DOMContentLoaded', function () {
  var msgBox = document.getElementById('cart-msg');

  // Thông báo cảnh báo trên trang giỏ hàng
  function showMessage(text) {
    if (!msgBox) return;
    msgBox.textContent = text;
    msgBox.style.display = text ? 'block' : 'none';
  }

  // Cập nhật số nhỏ trên biểu tượng giỏ hàng ở menu
  function setBadge(count) {
    var badge = document.querySelector('.badge');
    if (badge) badge.textContent = count;
  }

  // Thông báo nhỏ ở góc màn hình (dùng khi thêm vào giỏ)
  function toast(text, level) {
    if (!text) return;
    var el = document.getElementById('cart-toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'cart-toast';
      el.style.cssText = 'position:fixed;right:16px;bottom:16px;max-width:320px;padding:12px 16px;' +
        'border-radius:8px;color:#fff;box-shadow:0 4px 12px rgba(0,0,0,.25);z-index:1000;font-size:.95rem';
      document.body.appendChild(el);
    }
    var colors = { success: '#2e7d32', warning: '#ef6c00', danger: '#c62828' };
    el.style.background = colors[level] || colors.success;
    el.textContent = text;
    el.style.display = 'block';
    clearTimeout(el.hideTimer);
    el.hideTimer = setTimeout(function () { el.style.display = 'none'; }, 2500);
  }

  // Gửi form tới cart.php bằng fetch và nhận JSON
  // Lưu ý: không dùng form.action vì form có ô input tên "action" nên form.action trả về ô input đó, không phải đường dẫn
  function post(form) {
    var url = form.getAttribute('action') || window.location.href;
    return fetch(url, {
      method: 'POST',
      body: new FormData(form),
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (res) { return res.json(); });
  }

  // 1. Đổi số lượng trong trang giỏ hàng
  document.querySelectorAll('form.js-cart-update').forEach(function (form) {
    var input = form.querySelector('input[name="qty"]');
    var button = form.querySelector('button');
    if (button) button.style.display = 'none'; // đã tự cập nhật nên ẩn nút

    function send() {
      post(form)
        .then(function (data) {
          if (!data.ok) { location.reload(); return; }
          input.value = data.qty;
          form.closest('tr').querySelector('.js-subtotal').textContent = data.subtotal;
          document.querySelector('.js-total').textContent = data.total;
          setBadge(data.count);
          showMessage(data.message);
        })
        .catch(function () { form.submit(); }); // lỗi mạng thì gửi form như bình thường
    }

    input.addEventListener('change', send);
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      send();
    });
  });

  // 2. Bấm "Thêm vào giỏ" ở bất kỳ trang nào: giữ nguyên vị trí cuộn
  document.querySelectorAll('input[name="action"][value="add"]').forEach(function (flag) {
    var form = flag.form;
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var button = form.querySelector('button');
      if (button) button.disabled = true;
      post(form)
        .then(function (data) {
          setBadge(data.count);
          toast(data.message, data.level);
        })
        .catch(function () { form.submit(); })
        .then(function () { if (button) button.disabled = false; });
    });
  });
});

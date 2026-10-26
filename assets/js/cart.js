// Cập nhật số lượng trong giỏ hàng mà không cần tải lại trang
document.addEventListener('DOMContentLoaded', function () {
  var msgBox = document.getElementById('cart-msg');

  function showMessage(text) {
    if (!msgBox) return;
    msgBox.textContent = text;
    msgBox.style.display = text ? 'block' : 'none';
  }

  document.querySelectorAll('form.js-cart-update').forEach(function (form) {
    var input = form.querySelector('input[name="qty"]');
    var button = form.querySelector('button');
    if (button) button.style.display = 'none'; // đã tự cập nhật nên ẩn nút

    function send() {
      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (!data.ok) { location.reload(); return; }
          input.value = data.qty;
          form.closest('tr').querySelector('.js-subtotal').textContent = data.subtotal;
          document.querySelector('.js-total').textContent = data.total;
          var badge = document.querySelector('.badge');
          if (badge) badge.textContent = data.count;
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
});

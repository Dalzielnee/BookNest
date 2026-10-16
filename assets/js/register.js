document.addEventListener('DOMContentLoaded', function () {
  var pw = document.getElementById('password');
  var pw2 = document.getElementById('password2');
  var matchMsg = document.getElementById('pw-match');

  var rules = {
    len: function (v) { return v.length >= 8; },
    lower: function (v) { return /[a-z]/.test(v); },
    upper: function (v) { return /[A-Z]/.test(v); },
    digit: function (v) { return /[0-9]/.test(v); }
  };

  function checkRules() {
    document.querySelectorAll('#pw-rules li').forEach(function (li) {
      li.classList.toggle('ok', rules[li.dataset.rule](pw.value));
    });
  }

  function checkMatch() {
    matchMsg.hidden = pw2.value === '' || pw2.value === pw.value;
  }

  pw.addEventListener('input', function () { checkRules(); checkMatch(); });
  pw2.addEventListener('input', checkMatch);

  document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.target);
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('showing', show);
      btn.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    });
  });

  checkRules();
});
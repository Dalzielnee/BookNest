<?php
function product_card(array $p): void { ?>
  <article class="product">
    <a href="<?= url('product.php?id=' . $p['id']) ?>"><img src="<?= e(cover_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy"></a>
    <div class="body">
      <a class="title" href="<?= url('product.php?id=' . $p['id']) ?>"><?= e($p['title']) ?></a>
      <span class="author"><?= e($p['author']) ?></span>
      <span class="price"><?= money($p['price']) ?></span>
      <?php if ($p['stock'] > 0): ?>
      <form action="<?= url('cart.php') ?>" method="post">
        <?= csrf_field() ?><input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <button class="btn btn-sm">Thêm vào giỏ</button>
      </form>
      <?php else: ?><span class="muted">Hết hàng</span><?php endif; ?>
    </div>
  </article>
<?php }

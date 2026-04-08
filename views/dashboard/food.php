<div class="topbar">FOOD</div>

<div class="table">
<div class="row header"><span>Name</span><span>Status</span><span>Action</span></div>

<?php foreach($data as $s): ?>
<div class="row">
<span><?= $s['name'] ?></span>
<span>
    <div class="status-dot <?= $s['food'] ? 'status-yes' : 'status-no' ?>"></div>
</span>
<a class="toggle-btn" href="?page=food&food&id=<?= $s['id'] ?>">Toggle</a></div>
<?php endforeach; ?>
</div>
<div class="topbar">LAUNDRY</div>

<div class="table">
<div class="row header"><span>Name</span><span>Status</span><span>Action</span></div>

<?php foreach($data as $s): ?>
<div class="row">
<span><?= $s['name'] ?></span>
<span>
    <div class="status-bar">
        <div class="status-fill <?= $s['laundry'] ? 'status-yes' : 'status-no' ?>"></div>
    </div>
</span>
<span><a href="?page=laundry&laundry&id=<?= $s['id'] ?>">Toggle</a></span>
</div>
<?php endforeach; ?>
</div>
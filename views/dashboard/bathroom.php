<h3>Bathroom Cleaning</h3>

<div class="table">

<div class="row header">
<span>Name</span>
<span>Status</span>
<span>Action</span>
</div>

<?php foreach($data as $s): ?>
<div class="row">
<span><?= $s['name'] ?></span>

<span>
    <div class="status-bar">
        <div class="status-fill <?= $s['bathroom'] ? 'status-yes' : 'status-no' ?>"></div>
    </div>
</span>

<span>
<a href="?page=bathroom&bathroom&id=<?= $s['id'] ?>">Toggle</a>
</span>
</div>
<?php endforeach; ?>

</div>
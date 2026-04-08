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

<span class="<?= $s['bathroom']?'yes':'no' ?>">
<?= $s['bathroom']?'Clean':'Dirty' ?>
</span>

<span>
<a href="?page=bathroom&bathroom&id=<?= $s['id'] ?>">Toggle</a>
</span>
</div>
<?php endforeach; ?>

</div>
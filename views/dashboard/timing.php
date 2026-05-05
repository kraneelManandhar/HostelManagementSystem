<div class="topbar">TIME RECORD</div>

<?php foreach($data as $s): ?>
<form method="POST" class="row">
<span><?= $s['name'] ?></span>
<input type="time" name="in" value="<?= $s['time_in'] ?>">
<input type="time" name="out" value="<?= $s['time_out'] ?>">
<input type="hidden" name="id" value="<?= $s['id'] ?>">
<button name="timing">Save</button>
</form>
<?php endforeach; ?>
<div class="topbar">TIME RECORD</div>

<?php foreach($data as $s): ?>
<form method="POST" class="row">
<span><?= $s['name'] ?></span>
<input type="time" name="in" value="<?= $s['time_in'] ?>">
<input type="time" name="out" value="<?= $s['time_out'] ?>">
<input type="hidden" name="id" value="<?= $s['id'] ?>">
<button name="timing">Save</button>
</form>
<?php endforeach; ?>
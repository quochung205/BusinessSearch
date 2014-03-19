<?php

require_once 'init.php';

$sess = $DB->query('SELECT * FROM `session`')->rows();

$stype = array('Corporation Name', 'Limited', 'Entity Number');
    
foreach ($sess as $r):
    $count = ($r->page - 1) * 10 + $r->idx;
?>
<p data-type="<?php echo $r->type ?>" data-kw="<?php echo $r->keyword ?>">
    <input type="radio" name="sess" value="<?php echo $r->id ?>" />
    Type: <?php echo $stype[$r->type - 1] ?>,
    Keyword: <strong><?php echo $r->keyword ?></strong>,
    Tiến độ: <span class="label label-<?php echo ($count >= $r->total) ? 'success' : 'default' ?>"><?php echo $count . '/' . $r->total ?> (<?php echo round($count * 100 / $r->total, 1) ?>%)</span>
</p>

<?php endforeach; ?>
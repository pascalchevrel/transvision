<table>
    <tr>
        <th>Firefox Aurora</th>
        <th>Missing strings</th>
        <th>(in Devtools)</th>
        <th>Missing strings <br>devtools excluded</th>
    </tr>

    <?php foreach($results as $k => $v): ?>
    <tr id="<?=$k?>">
        <th><a href="#<?=$k?>"><?=$k?></a></th>
        <td style="text-align:right;"><?=$v['total_missing']?></td>
        <td style="text-align:right;"><?=$v['devtools_missing']?></td>
        <td style="text-align:right;"><?=$v['total_missing'] - $v['devtools_missing']?></td>
    </tr>
    <?php endforeach; ?>

</table>

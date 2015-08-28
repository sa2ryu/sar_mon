<?php
// データ取得
function getSar($opt = 'u',$max = 7){
$com = '/usr/bin/sar';
$log = '/var/log/sa/';
$time = time();
$res = array();
for($i=$max;$i>=0;$i--){
	$d = date('d',strtotime(sprintf('-%d day',$i),$time));
	$excom = sprintf('%s -%s -f %ssa%s',$com,$opt,$log,$d);
	unset($out);
	exec($excom,$out,$stat);
	// execのエラーを拾う

	$name = preg_split('/\s+/',$out[2]);
	$name[0] = 'time';
	foreach($out as $line){
		$line = preg_split('/\s+/',$line);
		$line[0] = preg_replace('/^(\d\d)時(\d\d)分(\d\d)秒$/','${1}:${2}:${3}',$line[0]);
		if(
			!preg_match('/^\d\d:\d\d:\d\d$/',$line[0])
		){
			continue;
		}
		if($name[1] == $line[1]){
			continue;
		}
		$tmpd = date('Y/m/d',strtotime(sprintf('-%d day',$i),$time)).' '.$line[0];
		$line[0] = strtotime($tmpd);
		foreach($line as $k => $v){
			$res[$name[$k]][] = $v;
		}
	}
}
return $res;
}

// グラフ出力
?>
<?php
predraw();
$result = getSar('u');
echo '<h2>待機中のCPU使用率</h2>';
draw('cpuidle',$result['time'],$result['%idle']);
$result = getSar('q');
echo '<h2>過去5分間のロードアベレージ</h2>';
draw('ldavg5',$result['time'],$result['ldavg-5']);
$result = getSar('b');
echo '<h2>1秒あたりの読み込みデータ量</h2>';
draw('breads',$result['time'],$result['bread/s']);
echo '<h2>1秒あたりの書き込みデータ量</h2>';
draw('bwrtns',$result['time'],$result['bwrtn/s']);
$result = getSar('n DEV');
echo '<h2>1秒あたりの受信データサイズ（KByte）</h2>';
draw('rxkbs',$result['time'],$result['rxkB/s']);
echo '<h2>1秒あたりの送信データサイズ（KByte）</h2>';
draw('txkbs',$result['time'],$result['txkB/s']);
?>

<?php
// drawの前に一度だけ実行が必要
function predraw(){
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1', {packages: ['corechart', 'line']});
</script>
<?php
}
// グラフ表示関数
function draw($name,$time,$plot){
?>
<div id="chart_<?php echo $name; ?>"></div>
<script type="text/javascript">
google.setOnLoadCallback(draw<?php echo $name; ?>);
function draw<?php echo $name; ?>() {
      var data = new google.visualization.DataTable();
      data.addColumn('datetime', 'Date');
      data.addColumn('number', '<?php echo $name; ?>');
      data.addRows([
<?php foreach($plot as $k => $v): ?>
        [new Date('<?php echo date(DATE_RFC2822,$time[$k]); ?>'),<?php echo $v; ?>],
<?php endforeach; ?>
      ]);
      var options = {
        hAxis: {
          title: 'Time'
        },
        vAxis: {
          title: '<?php echo $name; ?>'
        },
        colors: ['#0F0'],
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_<?php echo $name; ?>'));
      chart.draw(data, options);
    }
</script>
<?php } ?>

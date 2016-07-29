<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $this->fetch('title'); ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('style');
		echo $this->Html->css('//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<?php echo $this->Flash->render(); ?>
    <?php echo $this->fetch('content'); ?>

	<?=$this->Html->script('jquery.min.js')?>
	<?=$this->Html->script('bootstrap.min')?>
</body>
</html>
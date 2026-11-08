<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'CHIANUCCI MAURIZIO'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/site.css'); ?>
</head>
<body class="min-h-screen flex flex-col">
    <header class="bg-bianco border-b border-oro/40">
        <a href="/" class="inline-block p-4"><img src="<?php echo e(asset('brand/chianucci.svg')); ?>" alt="CHIANUCCI MAURIZIO" class="h-12"></a>
    </header>
    <main class="flex-1"><?php echo $__env->yieldContent('content'); ?></main>
</body>
</html>
<?php /**PATH /home/user/Chianucci/resources/views/layouts/site.blade.php ENDPATH**/ ?>
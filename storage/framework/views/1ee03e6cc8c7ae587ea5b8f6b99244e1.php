<?php
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $currentSlug = $currentSlug ?? null;
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title', 'CHIANUCCI MAURIZIO'); ?></title>
    <?php echo $__env->yieldPushContent('head'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/site.css', 'resources/js/app.js']); ?>
    <style>[x-cloak]{display:none !important}</style>
</head>
<body class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-50 bg-bianco border-b border-oro/40" x-data="{ menu: false }">
        <div class="mx-auto max-w-7xl flex items-center justify-between gap-4 px-6 py-3">
            <a href="<?php echo e(url("/$locale/")); ?>"><img src="<?php echo e(asset('brand/chianucci.svg')); ?>" alt="CHIANUCCI MAURIZIO" class="h-12"></a>
            <button class="md:hidden text-viola" @click="menu = !menu" :aria-expanded="menu" aria-label="Menu">☰</button>
            <nav class="hidden md:flex items-center gap-6" :class="{ '!flex flex-col absolute left-0 top-full w-full bg-bianco p-6 items-start': menu }">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = config('site.menu'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item === 'catalogo'): ?>
                        <?php if (isset($component)) { $__componentOriginalc6c85bb9195992d14c3dfbe16d5acc2d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc6c85bb9195992d14c3dfbe16d5acc2d = $attributes; } ?>
<?php $component = App\View\Components\MegaMenu::resolve(['locale' => $locale] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mega-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\MegaMenu::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc6c85bb9195992d14c3dfbe16d5acc2d)): ?>
<?php $attributes = $__attributesOriginalc6c85bb9195992d14c3dfbe16d5acc2d; ?>
<?php unset($__attributesOriginalc6c85bb9195992d14c3dfbe16d5acc2d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc6c85bb9195992d14c3dfbe16d5acc2d)): ?>
<?php $component = $__componentOriginalc6c85bb9195992d14c3dfbe16d5acc2d; ?>
<?php unset($__componentOriginalc6c85bb9195992d14c3dfbe16d5acc2d); ?>
<?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo e(SiteUrls::page($item, $locale)); ?>" class="nav-voce text-sm font-semibold uppercase tracking-wide hover:text-oro"><?php echo e(__('site.menu.'.$item)); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <a href="<?php echo e(SiteUrls::page('area-clienti', $locale)); ?>" class="btn-primario text-sm"><?php echo e(__('site.area_clienti')); ?></a>
                <?php if (isset($component)) { $__componentOriginal2816f070aad2a35f261d95e4abb88fcf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2816f070aad2a35f261d95e4abb88fcf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.language-switch','data' => ['locale' => $locale,'slug' => $currentSlug]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('language-switch'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['locale' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locale),'slug' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentSlug)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2816f070aad2a35f261d95e4abb88fcf)): ?>
<?php $attributes = $__attributesOriginal2816f070aad2a35f261d95e4abb88fcf; ?>
<?php unset($__attributesOriginal2816f070aad2a35f261d95e4abb88fcf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2816f070aad2a35f261d95e4abb88fcf)): ?>
<?php $component = $__componentOriginal2816f070aad2a35f261d95e4abb88fcf; ?>
<?php unset($__componentOriginal2816f070aad2a35f261d95e4abb88fcf); ?>
<?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="flex-1"><?php echo $__env->yieldContent('content'); ?></main>
    <?php if (isset($component)) { $__componentOriginal8a8716efb3c62a45938aca52e78e0322 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a8716efb3c62a45938aca52e78e0322 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $attributes = $__attributesOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $component = $__componentOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__componentOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/user/Chianucci/resources/views/layouts/site.blade.php ENDPATH**/ ?>
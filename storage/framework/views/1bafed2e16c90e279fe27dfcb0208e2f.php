<?php
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $t = $product->translated($locale);
    $fmt = fn ($v) => $v === null ? null : number_format((float) $v, 2, $locale === 'it' ? ',' : '.', '');
?>
<?php $__env->startSection('title', $t->name.' - CHIANUCCI MAURIZIO'); ?>

<?php $__env->startSection('content'); ?>
<section class="sezione"><div class="contenitore"><div class="griglia griglia-alto">
    <div class="col-meta">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <img src="<?php echo e(asset('storage/'.$image->path)); ?>" alt="<?php echo e($t->name); ?>" class="foto-prodotto mb-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <div class="col-meta">
        <a href="<?php echo e(SiteUrls::catalog($locale)); ?>" class="link-testo testo-piccolo"><?php echo e(__('site.catalog.back')); ?></a>
        <h1 class="mt-2"><?php echo e($t->name); ?></h1>
        <p><?php echo e(__('site.catalog.code')); ?> <strong><?php echo e($product->code); ?></strong></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t->description): ?><p><?php echo e($t->description); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <dl class="mt-6 grid grid-cols-2 gap-2 testo-piccolo">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->weight_g !== null): ?><dt><?php echo e(__('site.catalog.weight')); ?></dt><dd><?php echo e($fmt($product->weight_g)); ?></dd><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->length_cm !== null): ?><dt><?php echo e(__('site.catalog.length')); ?></dt><dd><?php echo e($fmt($product->length_cm)); ?></dd><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->link_mm !== null): ?><dt><?php echo e(__('site.catalog.link')); ?></dt><dd><?php echo e($fmt($product->link_mm)); ?></dd><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($product->titles)): ?><dt><?php echo e(__('site.catalog.titles')); ?></dt><dd><?php echo e(implode(', ', $product->titles)); ?></dd><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </dl>
        <div class="mt-8">
            <a href="<?php echo e(SiteUrls::page('area-clienti', $locale)); ?>" class="btn-primario"><?php echo e(__('site.catalog.request_quote')); ?></a>
            <p class="mt-4 testo-piccolo"><?php echo e(__('site.catalog.quote_note')); ?></p>
        </div>
    </div>
</div></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.site', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/user/Chianucci/resources/views/catalog/show.blade.php ENDPATH**/ ?>
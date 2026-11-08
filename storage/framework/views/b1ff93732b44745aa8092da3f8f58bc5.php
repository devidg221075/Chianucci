<?php
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $slug = config("site.catalog_slug.$locale");
?>
<?php $__env->startSection('title', __('site.catalog.title').' - CHIANUCCI MAURIZIO'); ?>

<?php $__env->startSection('content'); ?>
<section class="mx-auto max-w-7xl px-6 py-10">
    <h1 class="text-3xl font-bold mb-6"><?php echo e(__('site.catalog.title')); ?></h1>

    <form method="get" action="<?php echo e(url("/$locale/$slug")); ?>" class="grid gap-4 md:grid-cols-4 bg-bianco border border-oro/40 p-4 mb-8 text-sm">
        <label class="md:col-span-2"><?php echo e(__('site.catalog.search')); ?>

            <input type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" class="block w-full border border-oro/60 p-2 mt-1">
        </label>
        <label><?php echo e(__('site.catalog.category')); ?>

            <select name="categoria" class="block w-full border border-oro/60 p-2 mt-1">
                <option value=""><?php echo e(__('site.catalog.all_categories')); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($category->slug); ?>" <?php if(($filters['categoria'] ?? null) === $category->slug): echo 'selected'; endif; ?>><?php echo e($category->translated($locale)?->name ?? $category->slug); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </label>
        <label><?php echo e(__('site.catalog.metal')); ?>

            <select name="titolo" class="block w-full border border-oro/60 p-2 mt-1">
                <option value=""><?php echo e(__('site.catalog.all_metals')); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $metals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($metal); ?>" <?php if(($filters['titolo'] ?? null) === $metal): echo 'selected'; endif; ?>><?php echo e($metal); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </label>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['maglia' => 'link', 'lunghezza' => 'length', 'peso' => 'weight']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $param => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <fieldset class="md:col-span-1"><legend><?php echo e(__('site.catalog.'.$label)); ?></legend>
                <div class="flex gap-2 mt-1">
                    <input type="text" inputmode="decimal" name="<?php echo e($param); ?>_min" placeholder="<?php echo e(__('site.catalog.min')); ?>" value="<?php echo e(is_string($filters[$param.'_min'] ?? null) ? $filters[$param.'_min'] : ''); ?>" class="w-1/2 border border-oro/60 p-2">
                    <input type="text" inputmode="decimal" name="<?php echo e($param); ?>_max" placeholder="<?php echo e(__('site.catalog.max')); ?>" value="<?php echo e(is_string($filters[$param.'_max'] ?? null) ? $filters[$param.'_max'] : ''); ?>" class="w-1/2 border border-oro/60 p-2">
                </div>
            </fieldset>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="flex items-end gap-3">
            <button class="btn-primario" type="submit"><?php echo e(__('site.catalog.apply')); ?></button>
            <a href="<?php echo e(url("/$locale/$slug")); ?>" class="underline text-viola"><?php echo e(__('site.catalog.reset')); ?></a>
        </div>
    </form>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->isEmpty()): ?>
        <p class="py-12 text-center"><?php echo e(__('site.catalog.empty')); ?></p>
    <?php else: ?>
        <div class="grid gap-6 grid-cols-2 md:grid-cols-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $t = $product->translated($locale); $image = $product->images->first(); ?>
                <a href="<?php echo e(url("/$locale/prodotto/".$t->slug)); ?>" class="block bg-bianco border border-oro/30 hover:border-oro">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image): ?>
                        <img src="<?php echo e(asset('storage/'.$image->path)); ?>" alt="<?php echo e($t->name); ?>" loading="lazy" class="w-full aspect-square object-cover">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="p-3">
                        <h2 class="text-base font-semibold"><?php echo e($t->name); ?></h2>
                        <p class="text-xs"><?php echo e(__('site.catalog.code')); ?> <?php echo e($product->code); ?></p>
                    </div>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
        <div class="mt-8"><?php echo e($products->links()); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.site', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/user/Chianucci/resources/views/catalog/index.blade.php ENDPATH**/ ?>
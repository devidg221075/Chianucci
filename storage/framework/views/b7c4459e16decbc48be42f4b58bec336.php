<?php
    use App\Models\Category;
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $categories = Category::with('translations')->whereNotNull('image_path')->orderBy('sort')->limit(4)->get();
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->isNotEmpty()): ?>
<section class="py-16 px-6 bg-bianco"><div class="mx-auto max-w-6xl">
    <p class="text-xs font-semibold uppercase tracking-widest text-oro-scuro"><?php echo e($locale === 'it' ? 'I nostri prodotti' : 'Our products'); ?></p>
    <h2 class="mt-2 text-3xl font-bold"><?php echo e($locale === 'it' ? 'Catene per il mercato professionale' : 'Chains for the professional market'); ?></h2>
    <div class="mt-8 grid gap-6 grid-cols-2 md:grid-cols-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <a data-home-category href="<?php echo e(SiteUrls::catalog($locale, ['categoria' => $category->slug])); ?>" class="group block border border-oro/30 hover:border-oro">
                <img src="<?php echo e(asset('storage/'.$category->image_path)); ?>" alt="<?php echo e($category->translated($locale)?->name ?? $category->slug); ?>" loading="lazy" class="h-40 w-full object-contain bg-avorio">
                <span class="block p-4 font-semibold text-viola group-hover:text-oro-scuro"><?php echo e($category->translated($locale)?->name ?? $category->slug); ?></span>
            </a>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <p class="mt-8"><a href="<?php echo e(SiteUrls::catalog($locale)); ?>" class="btn-secondario"><?php echo e(__('site.catalog_all')); ?></a></p>
</div></section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /home/user/Chianucci/resources/views/home/categories.blade.php ENDPATH**/ ?>
<?php
    use App\Models\Category;
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $categories = Category::with('translations')->whereNotNull('image_path')->orderBy('sort')->limit(4)->get();
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->isNotEmpty()): ?>
<section class="sezione sezione-chiara"><div class="contenitore"><div class="griglia griglia-alto">
    <div class="col-intero">
        <p class="occhiello"><?php echo e($locale === 'it' ? 'I nostri prodotti' : 'Our products'); ?></p>
        <h2><?php echo e($locale === 'it' ? 'Catene per il mercato professionale' : 'Chains for the professional market'); ?></h2>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <a data-home-category href="<?php echo e(SiteUrls::catalog($locale, ['categoria' => $category->slug])); ?>" class="scheda col-quarto">
            <img src="<?php echo e(asset('storage/'.$category->image_path)); ?>" alt="<?php echo e($category->translated($locale)?->name ?? $category->slug); ?>" loading="lazy" class="foto-prodotto">
            <span class="scheda-testo"><?php echo e($category->translated($locale)?->name ?? $category->slug); ?></span>
        </a>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <p class="col-intero"><a href="<?php echo e(SiteUrls::catalog($locale)); ?>" class="btn-secondario"><?php echo e(__('site.catalog_all')); ?></a></p>
</div></div></section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /home/user/Chianucci/resources/views/home/categories.blade.php ENDPATH**/ ?>
<?php use App\Support\SiteUrls; ?>
<div class="relative" x-data="{ open: false }" @mouseenter="open = window.innerWidth >= 768" @mouseleave="open = false" @keydown.escape="open = false">
    <a href="<?php echo e(SiteUrls::catalog($locale)); ?>" class="nav-voce text-sm font-semibold uppercase tracking-wide hover:text-oro"
       @click="if (window.innerWidth < 768) { $event.preventDefault(); open = !open }"
       :aria-expanded="open"><?php echo e(__('site.menu.catalogo')); ?></a>
    <div x-show="open" x-cloak x-transition
         class="md:absolute md:left-1/2 md:-translate-x-1/2 md:top-full z-40 mt-2 md:w-[min(72rem,95vw)] bg-bianco border border-oro/40 shadow-xl p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $t = $category->translated($locale); ?>
                <a data-mega-tile href="<?php echo e(SiteUrls::catalog($locale, ['categoria' => $category->slug])); ?>"
                   class="flex md:flex-col items-center md:items-stretch gap-3 group">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->image_path): ?>
                        <img src="<?php echo e(asset('storage/'.$category->image_path)); ?>" alt="" loading="lazy"
                             class="h-12 w-12 md:h-28 md:w-full object-cover">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <span class="text-sm font-semibold text-viola group-hover:text-oro"><?php echo e($t?->name ?? $category->slug); ?></span>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
        <a href="<?php echo e(SiteUrls::catalog($locale)); ?>" class="btn-secondario mt-4 text-sm"><?php echo e(__('site.catalog_all')); ?></a>
    </div>
</div>
<?php /**PATH /home/user/Chianucci/resources/views/components/mega-menu.blade.php ENDPATH**/ ?>
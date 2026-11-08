<?php use App\Support\SiteUrls; ?>
<div x-data="{ open: false }" @mouseenter="open = window.innerWidth >= 768" @mouseleave="open = false" @keydown.escape="open = false">
    <a href="<?php echo e(SiteUrls::catalog($locale)); ?>" class="nav-voce hover:text-oro-scuro"
       @click="if (window.innerWidth < 768) { $event.preventDefault(); open = !open }"
       :aria-expanded="open"><?php echo e(__('site.menu.catalogo')); ?></a>
    <div x-show="open" x-cloak x-transition
         class="w-full md:absolute md:inset-x-0 md:top-full z-40 mt-2 md:mt-0 bg-bianco border border-oro/40 shadow-xl py-6">
        <div class="contenitore">
            <div class="griglia griglia-alto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $t = $category->translated($locale); ?>
                    <a data-mega-tile href="<?php echo e(SiteUrls::catalog($locale, ['categoria' => $category->slug])); ?>" class="col-sesto group">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->image_path): ?>
                            <img src="<?php echo e(asset('storage/'.$category->image_path)); ?>" alt="" loading="lazy" class="foto-prodotto">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="block mt-2 testo-piccolo font-semibold text-viola group-hover:text-oro-scuro"><?php echo e($t?->name ?? $category->slug); ?></span>
                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <p class="mt-6"><a href="<?php echo e(SiteUrls::catalog($locale)); ?>" class="btn-secondario"><?php echo e(__('site.catalog_all')); ?></a></p>
        </div>
    </div>
</div>
<?php /**PATH /home/user/Chianucci/resources/views/components/mega-menu.blade.php ENDPATH**/ ?>
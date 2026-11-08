<?php $c = config('site.company'); ?>
<footer class="sezione sezione-scura testo-piccolo">
    <div class="contenitore">
        <div class="griglia griglia-alto">
            <div class="col-terzo">
                <img src="<?php echo e(asset('brand/chianucci-bianco.svg')); ?>" alt="<?php echo e($c['name']); ?>" class="h-12 mb-4">
                <p><?php echo e($c['name']); ?></p>
                <p><?php echo e($c['address']); ?></p>
            </div>
            <div class="col-terzo">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $c['phones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <p><a href="tel:<?php echo e(preg_replace('/[^+0-9]/', '', $phone)); ?>"><?php echo e($phone); ?></a></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <p><a href="mailto:<?php echo e($c['email']); ?>"><?php echo e($c['email']); ?></a></p>
                <p>PEC: <?php echo e($c['pec']); ?></p>
            </div>
            <div class="col-terzo">
                <p>P.IVA e C.F. <?php echo e($c['vat']); ?></p>
                <p>REA n. <?php echo e($c['rea']); ?></p>
                <p>Cap. Soc. <?php echo e($c['capital']); ?></p>
                <p><?php echo e($c['register']); ?></p>
                <p class="mt-2"><a class="underline" href="<?php echo e(\App\Support\SiteUrls::page('privacy', app()->getLocale())); ?>">Privacy Policy</a></p>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH /home/user/Chianucci/resources/views/components/footer.blade.php ENDPATH**/ ?>
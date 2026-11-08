Nuova richiesta dal sito chianucci.it (<?php echo e(strtoupper($lang)); ?>)

Nome: <?php echo e($data['nome']); ?>

Azienda: <?php echo e($data['azienda'] ?? '-'); ?>

Email: <?php echo e($data['email']); ?>

Telefono: <?php echo e($data['telefono'] ?? '-'); ?>


Messaggio:
<?php echo e($data['messaggio']); ?>

<?php /**PATH /home/user/Chianucci/resources/views/mail/contact.blade.php ENDPATH**/ ?>
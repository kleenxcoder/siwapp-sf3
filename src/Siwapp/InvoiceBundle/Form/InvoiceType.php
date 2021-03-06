<?php

namespace Siwapp\InvoiceBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Siwapp\CoreBundle\Form\AbstractInvoiceType;
use Siwapp\InvoiceBundle\Entity\Item;
use Siwapp\InvoiceBundle\Form\ItemType;

class InvoiceType extends AbstractInvoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('issue_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'form.issue_date',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('due_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'form.due_date',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('forcefully_closed', null, [
                'label' => 'form.forcefully_closed',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
            ->add('sent_by_email', null, [
                'label' => 'form.sent_by_email',
                'translation_domain' => 'SiwappInvoiceBundle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Siwapp\InvoiceBundle\Entity\Invoice',
        ]);
    }
}

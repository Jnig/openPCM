<?php

namespace Jan\ZevirtBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Jan\ZevirtBundle\Form\EventListener\ExtraFieldSubscriber;

class StorageIscsiType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $subscriber = new ExtraFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);

        $builder
                ->add('name')
                ->add('hostname')
                ->add('devicePath')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Jan\ZevirtBundle\Entity\StorageIscsi',
            'csrf_protection' => false
        ));
    }

    public function getName() {
        return '';
    }

}

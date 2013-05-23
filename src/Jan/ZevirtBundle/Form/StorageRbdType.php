<?php

namespace Jan\ZevirtBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Jan\ZevirtBundle\Form\EventListener\ExtraFieldSubscriber;

class StorageRbdType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $subscriber = new ExtraFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);

        $builder
                ->add('name')
                ->add('pool')
                ->add('username')
                ->add('secret')
                ->add('hosts')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Jan\ZevirtBundle\Entity\StorageRbd',
            'csrf_protection' => false
        ));
    }

    public function getName() {
        return '';
    }

}

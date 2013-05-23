<?php

namespace Jan\ZevirtBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Jan\ZevirtBundle\Form\EventListener\ExtraFieldSubscriber;

class DiskOtherType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $subscriber = new ExtraFieldSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);

        $builder
                ->add('driverType')
                ->add('diskDevice')
                ->add('path')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Jan\ZevirtBundle\Entity\Disk',
            'csrf_protection' => false
        ));
    }

    public function getName() {
        return '';
    }

}

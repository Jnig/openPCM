<?php

namespace Jan\ZevirtBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Jan\ZevirtBundle\Form\EventListener\ExtraFieldSubscriber;
use Jan\ZevirtBundle\Form\EventListener\NodeFieldSubscriber;

class VirtualMachineType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $subscriber = new ExtraFieldSubscriber($builder->getFormFactory());
        $subscriber2 = new NodeFieldSubscriber($builder->getFormFactory());

        $builder->addEventSubscriber($subscriber);
        $builder->addEventSubscriber($subscriber2);

        $builder
                ->add('name')
                ->add('vcpu')
                ->add('memory')
                ->add('bootDev')

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Jan\ZevirtBundle\Entity\VirtualMachine',
            'csrf_protection' => false
        ));
    }

    public function getName() {
        return '';
    }

}

<?php
namespace Siwapp\InvoiceBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Siwapp\InvoiceBundle\Entity\Invoice;
use SIwapp\InvoiceBundle\SiwappInvoiceBundle;
use Symfony\Component\Yaml\Parser;
use Doctrine\Common\Util\Inflector;

class LoadInvoiceData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
  private $container;

  public function setContainer(ContainerInterface $container = null)
  {
    $this->container = $container;
  }

    public function load($manager)
    {

      $yaml = new Parser();
      // TODO: find a way of obtainin Bundle's path with the help of $this->container
      $bpath = './src/Siwapp/InvoiceBundle';
      $value = $yaml->parse(file_get_contents($bpath.'/DataFixtures/invoices.yml'));
      
      foreach($value['Invoice'] as $ref => $values)
      {
	$invoice = new Invoice();
	foreach($values as $fname => $fvalue)
	{
	  $method = 'set'.Inflector::camelize($fname);
	  if(is_callable(array($invoice, $method)))
	  {
	    call_user_func(array($invoice, $method), $fvalue);
	  }
	}
	$manager->persist($invoice);
	$manager->flush();
	$this->addReference($ref, $invoice);
      }

    }

    public function getOrder()
    {
      return '2';
    }
}
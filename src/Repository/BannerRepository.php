<?php

namespace App\Repository;

use App\Entity\Banners;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Banners|null find($id, $lockMode = null, $lockVersion = null)
 * @method Banners|null findOneBy(array $criteria, array $orderBy = null)
 * @method Banners[]    findAll()
 * @method Banners[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Banners::class);
    }

    public function findAllPagination($obj){
//        $em    = $obj->get('doctrine.orm.entity_manager');
//        $dql   = "SELECT u FROM App:Banners u ORDER BY u.bannerid DESC";
//        $query = $em->createQuery($dql);
//
//        $paginator  = $obj->get('knp_paginator');
//        $pagination = $paginator->paginate(
//            $query,
//            $obj->get('request')->query->get('page', 1),
//            30
//        );
        $pagination = $this->findBy(array(),array('id'=>'desc'));
        foreach($pagination as $i => $banner){
            $location = array();
            $array = $banner->getLocation();
            if(in_array(0, $array))
                $location[] = "Left";
            if(in_array(1, $array))
                $location[] = "Center";
            if(in_array(2, $array))
                $location[] = "Right";
            if(in_array(3, $array))
                $location[] = "Fixed right";
            if(in_array(4, $array))
                $location[] = "Fixed left";
            if(in_array(5, $array))
                $location[] = "User profile";
            $pagination[$i]->bannerLocation = implode(', ', $location);
            $pagination[$i]->activeClass = (($banner->getActive()=='1') ? 'green checkmark' : 'red cancel circle basic');
            $pagination[$i]->image = $this->getBannersPath() . $banner->getId() . '.' . $banner->getFileExt();
        }

        return $pagination;
    }

    public function getFormOptions($id, $page, $route){
        $action = ($id > 0) ? 'Edit' : 'Add';
        $formIcon = ($id > 0) ? 'save' : 'add sign';
        $buttonValue = ($id > 0) ? 'Save' : 'Add';
        $banner = $this->find($id);

        if($id > 0) {
            $image = $banner;
            $image->src = $this->getBannersPath() . $banner->getId() . '.' . $banner->getFileExt();
            $image->ext = $banner->getFileExt();
            $image->bannerWidth = $banner->getWidth();
            $image->bannerheight = $banner->getHeight();
        } else $image = false;

        $parameters = ($page == 1) ? array() : array('page' => $page);
        $backUrl = $route->generate('admin_banners', $parameters);

        return array(   'action'    => $action,
            'icon'      => $formIcon,
            'button'    => $buttonValue,
            'image'     => $image,
            'bannersUrl' => $backUrl);
    }

    public function saveBanner($banner, $files, $bannerfileext){

        foreach ($files as $uploadedFile){
            $file = $uploadedFile['bannerfileext'];
            if($file){
                $name = $file->getClientOriginalName();
                $nameArray = explode('.', $name);
                $bannerfileext = $nameArray[1];
            }
        }

        $em = $this->_em;
        if($bannerfileext) $banner->setFileExt($bannerfileext);
        $em->persist($banner);
        $em->flush();

        if($file){
            $file->move($this->getBannersPath($_SERVER['DOCUMENT_ROOT']), $banner->getId() . '.' . $bannerfileext);
            chmod($this->getBannersPath($_SERVER['DOCUMENT_ROOT']) . $banner->getId() . '.' . $bannerfileext, 0777);
        }

        return true;
    }


    public function getBannersPath($root = false){
        if(!$root)$root = 'https://' . $_SERVER['HTTP_HOST'];

        return $root . '/uploads/banners/';
    }

    // /**
    //  * @return Banners[] Returns an array of Banners objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Banners
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

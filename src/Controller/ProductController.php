<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/product')]
class ProductController extends AbstractController
{
   public function __construct(ManagerRegistry $managerRegistry)
   {
      $this->managerRegistry = $managerRegistry;
   } 
    #[Route('/index', name: 'product_index')]
   public function productIndex(ProductRepository $productRepository)
   {
      $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
      //$products = $productRepository->sortProductByIdDesc();
      return $this->render(
         'product/index.html.twig',
         [
            'products' => $products
         ]
      );
   }
   #[Route('/store', name: 'product_store')]
   public function productStore()
   {
      $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
      return $this->render(
         'product/store.html.twig',
         [
            'products' => $products
         ]
      );
   }
   #[Route('/detail/{id}', name: 'product_detail')]
   public function productDetail($id, ProductRepository $productRepository)
   {
      $product = $productRepository->find($id);
      if ($product == null) {
         $this->addFlash('Warning', 'Invalid product id !');
         return $this->redirectToRoute('product_index');
      }
      return $this->render(
         'product/detail.html.twig',
         [
            'product' => $product
         ]
      );
   }
   #[Route('/delete/{id}', name: 'product_delete')]
   public function productDelete($id,ManagerRegistry $managerRegistry)
   {
      $product = $managerRegistry->getRepository(Product::class)->find($id);
      if ($product == null) {
         $this->addFlash('Warning', 'Product not existed !');
      } else {
         $manager = $managerRegistry->getManager();
         $manager->remove($product);
         $manager->flush();
         $this->addFlash('Info', 'Delete product succeed !');
      }
      return $this->redirectToRoute('product_index');
   }
   #[Route('/add', name: 'product_add')]
   public function productAdd (Request $request) {
      $product = new Product;
      $form = $this->createForm(ProductType::class,$product);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
         $manager = $this->managerRegistry->getManager();
         $manager->persist($product);
         $manager->flush();
         $this->addFlash('Info', 'Add product succeed !');
         return $this->redirectToRoute("product_index");
      }
      return $this->renderForm("product/add.html.twig",
      [
            'productForm' => $form
      ]);
   }
   #[Route('/edit/{id}', name: 'product_edit')]
   public function productEdit($id, Request $request,ManagerRegistry $managerRegistry)
   {
      $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if ($product == null) {
            $this->addFlash('Warning', 'Product not existed !');
         } else {
            $form = $this->createForm(ProductType::class,$product);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $manager = $this->managerRegistry->getManager();
                $manager->persist($product);
                $manager->flush();
                $this->addFlash('Info', 'Edit product succeed !');
                return $this->redirectToRoute("product_index");
            }
            return $this->renderForm("product/edit.html.twig",
            [
                'productForm' => $form
            ]);
         }
   }



}

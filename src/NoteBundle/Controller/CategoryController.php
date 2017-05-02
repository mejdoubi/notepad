<?php

namespace NoteBundle\Controller;

use NoteBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategoryController extends Controller
{
    /**
     * @Route("/categories", name="categories")
     */
    public function listCategoriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('NoteBundle:Category')->findAll();
        if (!$categories) {
            throw $this->createNotFoundException('Categories not found');
        }
        return $this->render('NoteBundle:Categories:list.html.twig',
            array('categories' => $categories));
    }

    /**
     * @Route("/form/newCategory", name="newCategory")
     */
    public function newCategoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $category = new Category();

        $form = $this->createFormBuilder($category)
            ->add('label', TextType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('categories');
        }

        return $this->render('NoteBundle:Form:category.html.twig', array(
            'form' => $form->createView(), 'route' => 'new'));
    }

    /**
     * @Route("/form/editCategory/{id}", name="editCategory", requirements={"id": "\d+"})
     */
    public function editCategoryAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('NoteBundle:Category')->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $form = $this->createFormBuilder($category)
            ->add('label', TextType::class)
            ->add('submit', SubmitType::class)
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $em->flush();
            return $this->redirectToRoute('categories');
        }

        return $this->render('NoteBundle:Form:category.html.twig', array(
            'form' => $form->createView(), 'route' => 'edit'));
    }

    /**
     * @Route("/form/delCategory/{id}", name="delCategory", requirements={"id": "\d+"})
     */
    public function delCategoryAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('NoteBundle:Category')->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
        try {
            $em->remove($category);
            $em->flush();
        } catch(\Doctrine\DBAL\DBALException $e) {
            $this->addFlash(
            'notice',
            'This Category is already used!'
            );
            return $this->redirectToRoute('categories');
        }

        return $this->redirectToRoute('categories');
    }
}

?>
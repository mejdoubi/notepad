<?php

namespace NoteBundle\Controller;

use NoteBundle\Entity\Note;
use NoteBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function showHomeAction()
    {
        return $this->redirectToRoute('notes');
    }

    /**
     * @Route("/notes", name="notes")
     */
    public function listNotesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $notes = $em->getRepository('NoteBundle:Note')->findAll();
        if (!$notes) {
            throw $this->createNotFoundException('Notes not found');
        }

        $form = $this->createFormBuilder()
            ->add('search', SearchType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Search by tag'),
                'required' => false))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $searchedNotes = [];
            $search = trim($form->getData()['search']);
            $header = "<?xml version='1.0' encoding='UTF-8'?>";
            $query = '//content/tag';
            if (!empty($search)) {
                foreach ($notes as $note) {
                    $dom = new \DOMDocument();
                    $content = $note->getContent();
                    $xml = $header."<content>".$content."</content>";
                    try {
                        $dom->loadXML($xml);
                        $dom->schemaValidate("note.xsd");
                    } catch (\ErrorException $e) {
                        throw $this->createException('Something went wrong');
                    }
                    $xpath = new \DOMXpath($dom);
                    $entries = $xpath->query($query);
                    foreach ($entries as $entry) {
                        $elem = trim($entry->textContent);
                        if (strcasecmp($elem, $search) == 0) {
                            array_push($searchedNotes, $note);
                            break;
                        }
                    }
                }
                return $this->render('NoteBundle:Notes:list.html.twig',
                    array('notes' => $searchedNotes, 
                        'form' => $form->createView()));
            }
        }

        return $this->render('NoteBundle:Notes:list.html.twig',
            array('notes' => $notes, 'form' => $form->createView()));
    }

    /**
     * @Route("/notes/{id}", name="showNote", requirements={"id": "\d+"})
     */
    public function showNoteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            throw $this->createNotFoundException('Note not found');
        }
        return $this->render('NoteBundle:Notes:note.html.twig',
            array('note' => $note));
    }

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
     * @Route("/form/newNote", name="newNote")
     */
    public function newNoteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $note = new Note();

        $form = $this->createFormBuilder($note)
            ->add('title', TextType::class, array(
                'attr' => array('placeholder' => 'Enter title')))
            ->add('content', TextareaType::class, array(
                'attr' => array('rows' => '6',
                'placeholder' => 'Enter content')))
            ->add('tag', ButtonType::class, array(
                'label' => 'Add tag'))
            ->add('date', DateType::class)
            ->add('category', EntityType::class, array(
                'class' => 'NoteBundle:Category',
                'choice_label' => 'label',
                'label' => 'Choose Category'))
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->isValid()) {
                $note = $form->getData();
                $em->persist($note);
                $em->flush();
                return $this->redirectToRoute('showNote', 
                    array('id' => $note->getId()));
            }
            else {
                $this->addFlash(
                'notice',
                'You cannot add a tag inside another tag!'
                );
            }
        }

        return $this->render('NoteBundle:Form:note.html.twig', array(
            'form' => $form->createView(), 'route' => 'new'));
    }

    /**
     * @Route("/form/editNote/{id}", name="editNote", requirements={"id": "\d+"})
     */
    public function editNoteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            throw $this->createNotFoundException('Note not found');
        }

        $form = $this->createFormBuilder($note)
            ->add('title', TextType::class, array(
                'attr' => array('placeholder' => 'Enter title')))
            ->add('content', TextareaType::class, array(
                'attr' => array('rows' => '6',
                'placeholder' => 'Enter content')))
            ->add('tag', ButtonType::class, array(
                'label' => 'Add tag'))
            ->add('date', DateType::class)
            ->add('category', EntityType::class, array(
                'class' => 'NoteBundle:Category',
                'choice_label' => 'label',
                'label' => 'Choose Category'))
            ->add('submit', SubmitType::class)
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->isValid()) {
                $note = $form->getData();
                $em->flush();
                return $this->redirectToRoute('showNote', 
                    array('id' => $note->getId()));
            }
            else {
                $this->addFlash(
                'notice',
                'You cannot add a tag inside another tag!'
                );
            }
        }

        return $this->render('NoteBundle:Form:note.html.twig', array(
            'form' => $form->createView(), 'route' => 'edit'));
    }

    /**
     * @Route("/form/delNote/{id}", name="delNote", requirements={"id": "\d+"})
     */
    public function delNoteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $note = $em->getRepository('NoteBundle:Note')->find($id);
        if (!$note) {
            throw $this->createNotFoundException('Note not found');
        }
        $em->remove($note);
        $em->flush();
        return $this->redirectToRoute('notes');
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
<?php

namespace App\Controller;

use App\Entity\SchoolYear;
use App\Form\SchoolYearType;
use App\Repository\SchoolYearRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * @Route("/school-year")
 */
class SchoolYearController extends AbstractController
{
    /**
     * @Route("/", name="school_year_index", methods={"GET"})
     */
    public function index(SchoolYearRepository $schoolYearRepository): Response
    {
        return $this->render('school_year/index.html.twig', [
            'school_years' => $schoolYearRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="school_year_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $schoolYear = new SchoolYear();
        $form = $this->createForm(SchoolYearType::class, $schoolYear);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($schoolYear);
            $entityManager->flush();

            return $this->redirectToRoute('school_year_index');
        }

        return $this->render('school_year/new.html.twig', [
            'school_year' => $schoolYear,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="school_year_show", methods={"GET"})
     */
    public function show(SchoolYear $schoolYear): Response
    {
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_TEACHER')
            && !$this->isGranted('ROLE_STUDENT')
        ) {
            throw new AccessDeniedException();
        }

        if ($this->isGranted('ROLE_ADMIN')
        || $this->isGranted('ROLE_TEACHER')) {
            $schoolYears = $this->getUSer()->getSchoolYear();
            if (!$schoolYears->contains($schoolYear)) {
                throw new AccessDeniedException();
            }
        }

        return $this->render('school_year/show.html.twig', [
            'school_year' => $schoolYear,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="school_year_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, SchoolYear $schoolYear): Response
    {
        $form = $this->createForm(SchoolYearType::class, $schoolYear);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('school_year_index');
        }

        return $this->render('school_year/edit.html.twig', [
            'school_year' => $schoolYear,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="school_year_delete", methods={"DELETE"})
     */
    public function delete(Request $request, SchoolYear $schoolYear): Response
    {
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_TEACHER')
            && !$this->isGranted('ROLE_STUDENT')
        ) {
            throw new AccessDeniedException();
        }

        if ($this->isGranted('ROLE_ADMIN')
        || $this->isGranted('ROLE_TEACHER')) {
            // on vérifie si l'id du projet est bien dans la liste des projets de l'utilisateur
            $schoolYears = $this->getUSer()->getSchoolYear();
            //on va utiliser arraycollection
            if (!$schoolYears->contains($schoolYear)) {
                throw new AccessDeniedException();
            }

        }
        if ($this->isCsrfTokenValid('delete'.$schoolYear->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($schoolYear);
            $entityManager->flush();
        }

        return $this->redirectToRoute('school_year_index');
    }
}

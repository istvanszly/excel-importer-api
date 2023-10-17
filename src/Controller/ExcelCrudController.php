<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\BandsRepository;

class ExcelCrudController extends AbstractController
{
    private BandsRepository $bandsRepository;

    public function __construct(BandsRepository $bandsRepository)
    {
        $this->bandsRepository = $bandsRepository;
    }

    /**
     * @Route("/api/excel/list", name="app_excel_list")
     */
    public function excelList(): JsonResponse
    {
        return $this->json([
            'success'=>true,
            'data'=>$this->bandsRepository->getConnection()->executeQuery('SELECT * FROM bands ORDER BY id ASC')->fetchAll(),
            'message'=>'Bands successfully listed!'
        ]);
    }

    /**
     * @Route("/api/excel/delete/{id}", name="app_excel_delete", methods={"DELETE"})
     */
    public function delete($id): JsonResponse
    {

    }
}

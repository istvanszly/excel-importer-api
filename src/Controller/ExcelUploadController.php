<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Repository\BandsRepository;

class ExcelUploadController extends AbstractController
{
    private BandsRepository $bandsRepository;

    public function __construct(BandsRepository $bandsRepository)
    {
        $this->bandsRepository = $bandsRepository;
    }

    /**
     * @Route("/api/excel/upload", name="app_excel_upload")
     */
    public function excelUpload(Request $request): JsonResponse
    {
        try
        {
            $excelFile = $request->files->get('excelFile');
            $truncateBeforeInsert = $request->request->get('truncateBeforeInsert');

            $uploadsFolderPath = $this->getParameter('kernel.project_dir').'/public/uploads';

            if(!$excelFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)
            {
                return $this->json(['success'=>false, 'message'=>'File was not found!']);
            }

            $fileExtension = pathinfo($excelFile->getClientOriginalName(), PATHINFO_EXTENSION);

            if(empty($fileExtension) || (trim($fileExtension) !== 'xlsx' && trim($fileExtension) !== 'xls'))
            {
                return $this->json(['success'=>false, 'message'=>'Only xlsx and xls file types are supported!']);
            }

            $filePath = $uploadsFolderPath.'/'.md5(uniqid()).'.'.$fileExtension;

            $excelFile->move($uploadsFolderPath, $filePath);

            if(!file_exists($filePath))
            {
                return $this->json(['success'=>false, 'message'=>'Failed to save file to server!']);
            }

            if(!empty($truncateBeforeInsert) && intval($truncateBeforeInsert) === 1)
            {
                $this->bandsRepository->getConnection()->executeQuery('TRUNCATE TABLE bands');
            }

            $spreadsheet = IOFactory::load($filePath);

            $worksheet = $spreadsheet->getSheet(0);

            $highestRow = $worksheet->getHighestRow();

            $index = 1;

            $stmt = $this->bandsRepository->getConnection()->prepare('INSERT INTO bands(group_name, origin, city, debut_year, separation_year, founders, members, genre, `description`, updated_at, created_at) VALUES '.implode(', ', array_fill(0, $highestRow - 1, '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')));

            for($row = 2; $row <= $highestRow; $row++)
            {
                $stmt->bindValue($index, $worksheet->getCellByColumnAndRow(1, $row)->getValue());
                $stmt->bindValue($index + 1, $worksheet->getCellByColumnAndRow(2, $row)->getValue());
                $stmt->bindValue($index + 2, $worksheet->getCellByColumnAndRow(3, $row)->getValue());
                $stmt->bindValue($index + 3, $worksheet->getCellByColumnAndRow(4, $row)->getValue());
                $stmt->bindValue($index + 4, $worksheet->getCellByColumnAndRow(5, $row)->getValue());
                $stmt->bindValue($index + 5, $worksheet->getCellByColumnAndRow(6, $row)->getValue());
                $stmt->bindValue($index + 6, $worksheet->getCellByColumnAndRow(7, $row)->getValue());
                $stmt->bindValue($index + 7, $worksheet->getCellByColumnAndRow(8, $row)->getValue());
                $stmt->bindValue($index + 8, $worksheet->getCellByColumnAndRow(9, $row)->getValue());
                $stmt->bindValue($index + 9, null);
                $stmt->bindValue($index + 10, date('Y-m-d H:i:s'));

                $index += 11;
            }

            $stmt->execute();

            @unlink($filePath);

            return $this->json([
                'success'=>true,
                'message'=>'File successfully imported!'
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->json([
                'success'=>false,
                'message'=>'An error occured while trying to upload file!'
            ]);
        }
    }
}

<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MembersExport
{
    protected $memberIds;

    public function __construct(array $memberIds)
    {
        $this->memberIds = $memberIds;
    }

    public function download($filename = null)
    {
        $filename = $filename ?? 'members_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        $members = Member::whereIn('id', $this->memberIds)
            ->with('user', 'accounts')
            ->orderBy('gender', 'asc')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headings
        $headings = [
            'First Name', 'Middle Name', 'Last Name', 'Username', 'Email', 'Phone',
            'ID Number', 'ID Type', 'Date of Birth', 'Gender', 'Marital Status',
            'Occupation', 'Employer', 'Monthly Income',
            'Physical Address', 'Postal Address', 'City', 'County', 'Country',
            'Emergency Contact Name', 'Emergency Contact Phone', 'Emergency Contact Relationship',
            'Membership ID', 'Membership Status', 'Accounts Count', 'Date Joined',
        ];

        $sheet->fromArray($headings, null, 'A1');

        // Style header row
        $headerStyle = $sheet->getStyle('A1:Z1');
        $headerStyle->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F4E78'); // Dark blue

        $row = 2;
        foreach ($members as $member) {
            $sheet->setCellValue('A' . $row, $member->first_name);
            $sheet->setCellValue('B' . $row, $member->middle_name);
            $sheet->setCellValue('C' . $row, $member->last_name);
            $sheet->setCellValue('D' . $row, $member->user->username ?? '');
            $sheet->setCellValue('E' . $row, $member->user->email ?? '');
            $sheet->setCellValue('F' . $row, $member->user->phone ?? '');
            $sheet->setCellValue('G' . $row, $member->id_number);
            $sheet->setCellValue('H' . $row, $member->id_type);
            $sheet->setCellValue('I' . $row, optional($member->date_of_birth)->format('Y-m-d'));
            $sheet->setCellValue('J' . $row, $member->gender);
            $sheet->setCellValue('K' . $row, $member->marital_status);
            $sheet->setCellValue('L' . $row, $member->occupation);
            $sheet->setCellValue('M' . $row, $member->employer);
            $sheet->setCellValue('N' . $row, $member->monthly_income);
            $sheet->setCellValue('O' . $row, $member->physical_address);
            $sheet->setCellValue('P' . $row, $member->postal_address);
            $sheet->setCellValue('Q' . $row, $member->city);
            $sheet->setCellValue('R' . $row, $member->county);
            $sheet->setCellValue('S' . $row, $member->country);
            $sheet->setCellValue('T' . $row, $member->emergency_contact_name);
            $sheet->setCellValue('U' . $row, $member->emergency_contact_phone);
            $sheet->setCellValue('V' . $row, $member->emergency_contact_relationship);
            $sheet->setCellValue('W' . $row, $member->membership_id);
            $sheet->setCellValue('X' . $row, $member->membership_status);
            $sheet->setCellValue('Y' . $row, $member->accounts->count());
            $sheet->setCellValue('Z' . $row, optional($member->membership_date)->format('Y-m-d'));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer->save('php://output');
        exit;
    }
}

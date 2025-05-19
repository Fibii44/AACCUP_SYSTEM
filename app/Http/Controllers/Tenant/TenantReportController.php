<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use FPDF;
use App\Models\Area;
use App\Models\Parameter;
use App\Models\Indicator;
use App\Models\Upload;

// PDF class with custom footer
class PDF extends FPDF {
    protected $tenantName;
    protected $departmentName;
    protected $currentDateTime;
    protected $logoPath;
    
    function __construct($tenantName, $currentDateTime, $logoPath = null, $departmentName = null) {
        parent::__construct();
        $this->tenantName = $tenantName;
        $this->currentDateTime = $currentDateTime;
        $this->logoPath = $logoPath;
        $this->departmentName = $departmentName;
    }
    
    function Header() {
        // If logo is set, display it
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 10, 8, 20); // Reduced width from 25 to 18
        }
        
        // Add department name if available
        if ($this->departmentName) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 8, $this->departmentName, 0, 1, 'C');
        }
        
        // Add report title
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 8, 'Instrument Submission Report', 0, 1, 'C');
        $this->Ln(5);
        
        // Add horizontal line
        $this->Cell(0, 0, '', 'T');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $footerText = $this->tenantName . " | Generated: " . $this->currentDateTime . " | Page " . $this->PageNo();
        $this->Cell(0, 10, $footerText, 0, 0, 'C');
    }
}

class TenantReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        $instruments = Instrument::with([
            'areas.parameters.indicators.uploads.user'
        ])->orderBy('order')->get();
        return view('tenant.reports.index', compact('instruments'));
    }

    /**
     * Generate a PDF report preview
     */
    public function generate(Request $request)
    {
        $instrumentIds = $request->input('instruments', []);
        
        // If no specific instruments selected, get all
        if (empty($instrumentIds)) {
            $instruments = Instrument::with([
                'areas.parameters.indicators.uploads.user'
            ])->orderBy('order')->get();
        } else {
            $instruments = Instrument::with([
                'areas.parameters.indicators.uploads.user'
            ])->whereIn('id', $instrumentIds)->orderBy('order')->get();
        }
        
        if ($request->ajax()) {
            return view('tenant.reports.preview', compact('instruments'))->render();
        }
        
        return view('tenant.reports.preview', compact('instruments'));
    }

    /**
     * Download the PDF report
     */
    public function download(Request $request)
    {
        $request->validate([
            'instruments' => 'required|array',
            'instruments.*' => 'exists:instruments,id'
        ]);
        
        $instrumentIds = $request->input('instruments', []);
        $instruments = Instrument::with([
            'areas.parameters.indicators.uploads.user'
        ])->whereIn('id', $instrumentIds)->orderBy('order')->get();
        
        // Get tenant settings for the report footer
        $tenantSettings = TenantSetting::first();
        $tenantName = $tenantSettings ? $tenantSettings->site_name : tenant('id');
        $departmentName = $tenantSettings ? $tenantSettings->header_text : null;
        $currentDateTime = Carbon::now()->format('M d, Y h:i A');
        $logoUrl = $tenantSettings ? $tenantSettings->logo_url : null;
        $logoPath = null;
        if ($logoUrl) {
            $logoPath = sys_get_temp_dir() . '/tenant_logo_' . md5($logoUrl) . '.png';
            if (!file_exists($logoPath)) {
                @file_put_contents($logoPath, @file_get_contents($logoUrl));
            }
        }
        try {
            // Generate PDF report
            $pdf = new PDF($tenantName, $currentDateTime, $logoPath, $departmentName);
            $pdf->SetAutoPageBreak(true, 15); // 15mm bottom margin
            
            // Set default font
            $pdf->SetFont('Arial', '', 10);
            
            // Loop through each instrument (one per page)
            foreach ($instruments as $instrument) {
                // Add a new page for each instrument
                $pdf->AddPage();
                
                // Add page header with instrument name
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->Cell(0, 10, $instrument->name, 0, 1, 'C');
                $pdf->Ln(10);
                
                // Loop through areas
                if ($instrument->areas->count() > 0) {
                    foreach ($instrument->areas as $area) {
                        // Add area name
                        $pdf->SetFont('Arial', 'B', 12);
                        $pdf->Cell(0, 8, $area->name, 0, 1, 'L');
                        $pdf->Ln(5);
                        
                        // Loop through parameters
                        if ($area->parameters->count() > 0) {
                            foreach ($area->parameters as $parameter) {
                                // Add parameter name
                                $pdf->SetFont('Arial', 'B', 10);
                                $pdf->Cell(10, 8, '', 0, 0, 'L'); // Indent
                                $pdf->Cell(0, 8, $parameter->name, 0, 1, 'L');
                                $pdf->Ln(3);
                
                                // Loop through indicators
                                if ($parameter->indicators->count() > 0) {
                                    foreach ($parameter->indicators as $indicator) {
                                        // Add indicator name
                $pdf->SetFont('Arial', '', 10);
                                        $pdf->Cell(20, 8, '', 0, 0, 'L'); // Indent
                                        $pdf->Cell(0, 8, $indicator->name, 0, 1, 'L');
                                        
                                        // Loop through uploads
                                        if ($indicator->uploads->count() > 0) {
                                            foreach ($indicator->uploads as $upload) {
                                                $pdf->SetFont('Arial', '', 8);
                                                $pdf->Cell(30, 8, '', 0, 0, 'L'); // Indent
                                                $pdf->Cell(0, 8, '- ' . $upload->file_name . ' (Uploaded by: ' . $upload->user->name . ')', 0, 1, 'L');
                                            }
                                            $pdf->Ln(2);
                                        } else {
                                            $pdf->SetFont('Arial', '', 8);
                                            $pdf->Cell(30, 8, '', 0, 0, 'L'); // Indent
                                            $pdf->Cell(0, 8, '- No files uploaded', 0, 1, 'L');
                                            $pdf->Ln(2);
                                        }
                                    }
                                } else {
                                    $pdf->SetFont('Arial', '', 8);
                                    $pdf->Cell(20, 8, '', 0, 0, 'L'); // Indent
                                    $pdf->Cell(0, 8, '- No indicators', 0, 1, 'L');
                                    $pdf->Ln(2);
                                }
                            }
                        } else {
                            $pdf->SetFont('Arial', '', 8);
                            $pdf->Cell(10, 8, '', 0, 0, 'L'); // Indent
                            $pdf->Cell(0, 8, '- No parameters', 0, 1, 'L');
                            $pdf->Ln(2);
                        }
                        $pdf->Cell(0, 0, '', 'T'); // Draw a horizontal line
                        $pdf->Ln(5);
                    }
                } else {
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->Cell(0, 8, '- No areas', 0, 1, 'L');
                }
            }
            
            // Output the PDF
            $filename = 'instrument_submission_report_' . Carbon::now()->format('Ymd_His') . '.pdf';
            $pdfContent = $pdf->Output('S');
            // Clean up temp logo file
            if ($logoPath && file_exists($logoPath)) {
                @unlink($logoPath);
            }
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating PDF report: ' . $e->getMessage());
            // Clean up temp logo file in case of error
            if ($logoPath && file_exists($logoPath)) {
                @unlink($logoPath);
            }
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }
} 
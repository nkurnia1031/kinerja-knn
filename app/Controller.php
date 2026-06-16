<?php

namespace app;

use app\Fungsi;
use app\DB;
use \ParagonIE\EasyDB\EasyStatement;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

/**
 * Abstract Controller Class
 * 
 * Base controller providing common functionality for CRUD operations,
 * data filtering, pagination, and export capabilities.
 * 
 * @package app
 */
abstract class Controller
{
    /*
    |--------------------------------------------------------------------------
    | Configuration Properties
    |--------------------------------------------------------------------------
    */
    
    /** @var string Page title */
    protected $judul;
    
    /** @var string Controller link/identifier */
    protected $Link;
    
    /** @var string Icon class for the page */
    protected $Icon = 'fas fa-fw fa-home';
    
    /** @var string Main model class name */
    protected $mainModel;

    /*
    |--------------------------------------------------------------------------
    | Filter & Query Properties
    |--------------------------------------------------------------------------
    */
    
    /** @var array Allowed fields for WHERE clauses */
    protected $allowedWhere;
    
    /** @var array Fields to display as select filters */
    protected $selectFilter;
    
    /** @var array Custom WHERE conditions for select filters */
    protected $selectWhere = [];
    
    /** @var string Fulltext search columns */
    protected $fulltext;

    /*
    |--------------------------------------------------------------------------
    | Sorting Properties
    |--------------------------------------------------------------------------
    */
    
    /** @var string Default sort column */
    protected $SortBy = 'id';
    
    /** @var string Default sort direction */
    protected $SortWith = 'DESC';
    
    /** @var array Valid sort orders */
    private const ALLOWED_SORT_ORDERS = ['ASC', 'DESC'];

    /*
    |--------------------------------------------------------------------------
    | Data Formatting Properties
    |--------------------------------------------------------------------------
    */
    
    /** @var array Fields to strip non-numeric characters */
    protected $hapusHuruf;
    
    /** @var array Fields to format as currency */
    protected $formatUang;
    
    /** @var array File upload field names */
    protected $files;

    /*
    |--------------------------------------------------------------------------
    | Field Exclusion Properties
    |--------------------------------------------------------------------------
    */
    
    /** @var array Fields to exclude from general display */
    protected $except;
    
    /** @var array Fields to exclude from forms */
    protected $exceptForm = [];
    
    /** @var array Fields to exclude from Excel export */
    protected $exceptExcel = [];
    
    /** @var array Additional data to pass to views */
    protected $tambahan = [];

    /*
    |--------------------------------------------------------------------------
    | Request/Session Properties
    |--------------------------------------------------------------------------
    */
    
    /** @var mixed Current request object */
    protected $Request;
    
    /** @var mixed Current session object */
    protected $Session;

    /*
    |--------------------------------------------------------------------------
    | Getter & Setter Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Set the page title
     */
    public function SetJudul($judul)
    {
        $this->judul = $judul;
    }

    /**
     * Get the page title
     */
    public function GetJudul()
    {
        return $this->judul;
    }

    /**
     * Get database connection
     */
    public function GetDB()
    {
        return DB::con();
    }

    /**
     * Set allowed WHERE fields
     */
    public function SetAllowedWhere($allowedWhere)
    {
        $this->allowedWhere = $allowedWhere;
    }

    /**
     * Get allowed WHERE fields
     */
    public function GetAllowedWhere()
    {
        return $this->allowedWhere;
    }

    /*
    |--------------------------------------------------------------------------
    | View Configuration Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Prepare base data for index view
     */
    public function SetIndex($Request, $Session)
    {
        return [
            'judul'  => $this->judul,
            'induk'  => 'Master',
            'path'   => "Pages.{$this->Link}.Index",
            'link'   => $this->Link,
            'icon'   => $this->Icon,
        ];
    }

    /**
     * Render the index page
     */
    public function Index($Request, $Session, $blade)
    {
        $data = $this->SetIndex($Request, $Session);
        
        return $blade->run($data['path'], [
            'data'    => $data,
            'Request' => $Request,
            'Session' => $Session
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Hook to add custom filters
     */
    public function tambahFilter($filter)
    {
        return $filter;
    }

    /**
     * Hook to modify filters before output
     */
    public function setFilter($filter)
    {
        return $filter;
    }

    /**
     * Prepare filter configuration with data from database
     */
    public function prepareFilter($fields, $db, $tb)
    {
        $filter = $fields->only($this->allowedWhere)->toArray();
        $filter = $this->tambahFilter($filter);

        foreach ($this->selectFilter as $fieldName) {
            $tableName = $filter[$fieldName]->tb ?? $tb;
            $whereClause = $this->selectWhere[$fieldName] ?? null;

            $filter[$fieldName]->type = 'select';
            $filter[$fieldName]->isi = $this->fetchFilterOptions($db, $fieldName, $tableName, $whereClause);
        }

        return $filter;
    }

    /**
     * Fetch distinct options for a filter field
     */
    private function fetchFilterOptions($db, $fieldName, $tableName, $whereClause)
    {
        $sql = "SELECT DISTINCT {$fieldName} FROM {$tableName} {$whereClause} ORDER BY {$fieldName} ASC";
        $results = $db->run($sql);

        return collect($results)
            ->map(fn($item) => ['key' => $item->$fieldName, 'label' => $item->$fieldName])
            ->toArray();
    }

    /**
     * Build WHERE statement from filter values
     */
    private function buildFilterWhere($filter, $request, $where)
    {
        foreach ($filter as $field) {
            if (empty($request[$field->name])) {
                continue;
            }

            $value = $request[$field->name];
            $column = "{$field->tb}.{$field->name}";

            switch ($field->type) {
                case 'date':
                case 'datetime-local':
                    $where = $this->addDateRangeCondition($where, $column, $value);
                    break;
                default:
                    $values = is_array($value) ? $value : [$value];
                    $values = array_values(array_filter($values, fn($item) => $item !== null && $item !== ''));

                    if (!empty($values)) {
                        $where->in("{$column} IN (?*)", $values);
                    }
                    break;
            }
        }

        return $where;
    }

    /**
     * Add date range condition to WHERE statement
     */
    private function addDateRangeCondition($where, $column, $value)
    {
        $dates = explode(' - ', $value);
        $startDate = \DateTime::createFromFormat('d/m/Y', $dates[0])->format('Y-m-d');
        $endDate = \DateTime::createFromFormat('d/m/Y', $dates[1])->format('Y-m-d');

        $where->with("{$column} BETWEEN ? and ?", $startDate, $endDate);

        return $where;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Hook Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Hook to modify model before query execution
     */
    public function setModelBefore($model)
    {
        return $model;
    }

    /**
     * Hook to modify model/data after query execution
     */
    public function setModelAfter($model)
    {
        return $model;
    }

    /**
     * Hook to modify data before Excel export
     */
    public function setModelForExcel($data)
    {
        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | API Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Handle API request for index data
     */
    public function IndexApi($Request, $Session, $blade)
    {
        $this->Request = $Request;
        $this->Session = $Session;

        // Initialize model and request defaults
        $model = new $this->mainModel;
        $requestData = $this->initializeRequestDefaults($Request);
        
        // Prepare fields and filters
        $model->fields = collect($model->fields);
        $filter = $this->prepareFilter($model->fields, $model->getDB(), $model->getTable());

        // Build query statements
        $where = $this->buildQueryStatements($model, $filter, $requestData);
        
        // Execute query with hooks
        $model->setStatement($where['main']);
        $model->setStatementAfter($where['after']);
        $model = $this->setModelBefore($model);
        $model->all();
        $model = $this->setModelAfter($model);

        // Prepare output
        $output = $this->prepareApiOutput($model, $filter, $requestData);

        // Handle export or return JSON
        $this->handleApiResponse($output, $blade);
    }

    /**
     * Initialize request with default values
     */
    private function initializeRequestDefaults($Request)
    {
        $defaults = [
            'q'        => '',
            'SortBy'   => $this->SortBy,
            'SortWith' => $this->SortWith,
            'limit'    => '1000'
        ];

        if (!empty($Request)) {
            foreach ($Request as $key => $value) {
                $defaults[$key] = $value;
            }
        }

        // Validate sort order
        if (!in_array($defaults['SortWith'], self::ALLOWED_SORT_ORDERS)) {
            $defaults['SortWith'] = 'DESC';
        }

        return $defaults;
    }

    /**
     * Build main and after WHERE statements
     */
    private function buildQueryStatements($model, $filter, $request)
    {
        $where = EasyStatement::open()->setEmptyInStatementsAllowed(true);
        $afterWhere = EasyStatement::open()->setEmptyInStatementsAllowed(true);

        // Add fulltext search
        if (!empty($request['q'])) {
            $where->with("MATCH({$this->fulltext}) AGAINST (? IN BOOLEAN MODE)", $request['q']);
        }

        // Add filter conditions
        $where = $this->buildFilterWhere($filter, $request, $where);

        // Add sorting and limit
        $sortColumn = $model->getDB()->escapeIdentifier($request['SortBy']);
        $afterWhere->with(
            "ORDER BY {$model->getTable()}.{$sortColumn} {$request['SortWith']} limit ?",
            $request['limit']
        );

        return ['main' => $where, 'after' => $afterWhere];
    }

    /**
     * Prepare API response output
     */
    private function prepareApiOutput($model, $filter, $request)
    {
        $exceptForm = !empty($this->exceptForm) ? $this->exceptForm : $this->except;
        
        $filter = $this->setFilter($filter);
        $filter = array_values($filter);

        $fields = $model->fields->except($exceptForm);
        $fields->transform(function ($item) {
            unset($item->tb);
            return $item;
        });

        return [
            'fields'   => $fields->values(),
            'data'     => $model->data,
            'filter'   => $filter,
            'primary'  => $model->primary,
            'request'  => $request,
            'tambahan' => $this->tambahan
        ];
    }

    /**
     * Handle API response (JSON, Excel, or Print)
     */
    private function handleApiResponse($output, $blade)
    {
        $currentPage = $GLOBALS['hal'] ?? '';

        if ($currentPage === "{$this->Link}-XLSX") {
            $this->exportXLSX($output);
            die();
        }

        if ($currentPage === "{$this->Link}-Cetak") {
            $this->Cetak($output, $blade);
            die();
        }

        echo json_encode(['status' => true, 'data' => $output]);
    }

    /*
    |--------------------------------------------------------------------------
    | Print/Export Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Hook to adjust fields for print view
     */
    public function penyesuaianFieldCetak($data)
    {
        return $data;
    }

    /**
     * Rename request values to labels for display
     */
    public function renameRequest($data)
    {
        $filters = collect($data['filter'])->keyBy('name');

        foreach ($data['request'] as $name => $values) {
            if (!isset($filters[$name]) || empty($filters[$name]->isi)) {
                continue;
            }

            $labelMap = collect($filters[$name]->isi)->keyBy('key')->map->label;
            $data['request'][$name] = collect($values)
                ->map(fn($v) => $labelMap[$v] ?? $v)
                ->values()
                ->all();
        }

        return $data['request'];
    }

    /**
     * Render print view
     */
    public function Cetak( $data, $blade)
    {
        $viewData = [
            'judul'     => $this->judul,
            'path'      => "Pages.{$this->Link}.Cetak",
            'icon'      => 'fas fa-fw fa-home',
            'tglExport' => date('d/m/Y H:i:s')
        ];

        $data = array_merge($data, $viewData);
        $data = $this->penyesuaianFieldCetak($data);
        $data = $this->applyFilterValues($data);

        echo $blade->run($data['path'], ['data' => $data]);
    }

    /**
     * Apply filter values for display
     */
    private function applyFilterValues($data)
    {
        foreach ($data['filter'] as $index => $filter) {
            $value = $data['request'][$filter->name] ?? '-';
            $data['filter'][$index]->val = is_array($value) ? implode(', ', $value) : $value;
        }

        return $data;
    }

    /**
     * Export data to Excel file
     */
    public function exportXLSX($data)
    {
        $data['fields'] = $data['fields']->whereNotIn('name', $this->exceptExcel);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile("export/{$this->Link}.xlsx");

        $this->writeExcelHeader($writer, $data);
        $this->writeExcelFilters($writer, $data);
        $this->writeExcelData($writer, $data);

        $writer->close();
        
        echo "<script>window.location.href = 'export/{$this->Link}.xlsx'</script>";
    }

    /**
     * Write Excel header section
     */
    private function writeExcelHeader($writer, $data)
    {
        $titleStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(20)
            ->build();

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->build();

        $titleCell = WriterEntityFactory::createCell("Export Data {$this->judul}");
        $titleRow = WriterEntityFactory::createRow([$titleCell], $titleStyle);
        
        $dateRow = WriterEntityFactory::createRowFromArray(['Tanggal Export', date('d/m/Y H:i:s')]);
        $filterHeaderRow = WriterEntityFactory::createRowFromArray(['Filter Data'], $headerStyle);

        $writer->addRows([$titleRow, $dateRow, $filterHeaderRow]);
    }

    /**
     * Write Excel filter section
     */
    private function writeExcelFilters($writer, $data)
    {
        $boldStyle = (new StyleBuilder())->setFontBold()->build();

        // Write filter values
        foreach ($data['filter'] as $filter) {
            $value = $data['request'][$filter->name] ?? '-';
            $value = is_array($value) ? implode(', ', $value) : $value;
            $writer->addRow(WriterEntityFactory::createRowFromArray([$filter->label, $value]));
        }

        // Write search and sort info
        $metaRows = [
            ['Pencarian', $data['request']['q']],
            ['Urut Berdasarkan', $data['request']['SortBy']],
            ['Urut Dengan', $data['request']['SortWith']],
            ['Limit Data', $data['request']['limit']],
            ['']
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray($metaRows[0], $boldStyle));
        
        for ($i = 1; $i < count($metaRows); $i++) {
            $writer->addRow(WriterEntityFactory::createRowFromArray($metaRows[$i]));
        }
    }

    /**
     * Write Excel data section
     */
    private function writeExcelData($writer, $data)
    {
        $headerStyle = $this->createExcelBorderStyle(true);
        $dataStyle = $this->createExcelBorderStyle(false);

        // Write column headers
        $headers = $data['fields']->pluck('label')->toArray();
        $writer->addRow(WriterEntityFactory::createRowFromArray($headers, $headerStyle));

        // Write data rows
        $processedData = $this->setModelForExcel($data['data']);
        
        foreach ($processedData as $row) {
            $values = $this->extractRowValues($row, $data['fields']);
            $writer->addRow(WriterEntityFactory::createRowFromArray($values, $dataStyle));
        }
    }

    /**
     * Create Excel border style
     */
    private function createExcelBorderStyle($bold)
    {
        $border = (new BorderBuilder())
            ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->build();

        $builder = (new StyleBuilder())->setBorder($border);
        
        if ($bold) {
            $builder->setFontBold();
        }

        return $builder->build();
    }

    /**
     * Extract values from a data row for Excel
     */
    private function extractRowValues($row, $fields)
    {
        $values = [];

        foreach ($fields as $field) {
            $name = $field->name;
            $value = $row->$name ?? '';

            // Format currency
            if (in_array($name, $this->formatUang)) {
                $value = !empty($value) ? "Rp " . number_format($value) : '-';
            }

            // Format file URLs
            if (in_array($name, $this->files)) {
                $value = !empty($value) ? $GLOBALS['baseURL'] . "upload/" . $value : '-';
            }

            $values[] = $value;
        }

        return $values;
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Hook to modify input before processing
     */
    public function penambahanInput($Request)
    {
        return $Request;
    }

    /**
     * Main CRUD handler
     */
    public function CRUD($Request, $Session, $blade)
    {
        $model = new $this->mainModel;
        $Request = $this->preprocessRequest($Request);
        
        $output = ['msg' => ''];
        $status = $this->processFileUploads($Request, $output);

        if (isset($Request->update)) {
            $_SERVER['REQUEST_METHOD'] = 'PUT';
        }

        $model->getDB()->beginTransaction();

        try {
            $output = $this->executeCrudOperation($model, $Request, $output, $status);
            $this->logOperation($model, $Request, 'Normal');
            $model->getDB()->commit();
        } catch (\Exception $e) {
            $model->getDB()->rollBack();
            $status = false;
            $output['msg'] = $e->getMessage();
            $this->logOperation($model, $Request, 'Error', $e->getMessage());
        }

        echo json_encode(['status' => $status, 'data' => $output]);
    }

    /**
     * Preprocess request data (formatting, password hashing)
     */
    private function preprocessRequest($Request)
    {
        $Request = $this->penambahanInput($Request);

        // Strip non-numeric characters
        foreach ($this->hapusHuruf as $field) {
            if (isset($Request->input->$field)) {
                $Request->input->$field = Fungsi::HapusHuruf($Request->input->$field);
            }
        }

        // Remove currency formatting
        foreach ($this->formatUang as $field) {
            if (isset($Request->input->$field)) {
                $Request->input->$field = Fungsi::removeCurrencyFormat($Request->input->$field);
            }
        }

        // Hash password
        if (!empty($Request->input->password)) {
            $Request->input->password = password_hash($Request->input->password, PASSWORD_DEFAULT);
        } elseif (isset($Request->input->password)) {
            unset($Request->input->password);
        }

        return $Request;
    }

    /**
     * Process file uploads
     */
    private function processFileUploads($Request, &$output)
    {
        $status = true;

        foreach ($this->files as $field) {
            if (empty($_FILES['input']['name'][$field])) {
                continue;
            }

            $result = Fungsi::upload($_FILES['input'], $field);
            
            if ($result->status) {
                $Request->input->$field = $result->nama;
            } else {
                $status = false;
                $output['msg'] .= $result->error . "\n";
            }
        }

        return $status;
    }

    /**
     * Execute the appropriate CRUD operation
     */
    private function executeCrudOperation($model, $Request, $output, $status)
    {
        if (!$status) {
            return $output;
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'PUT':
                return $this->update($model, $Request, $output);
            case 'DELETE':
                return $this->hapus($model, $Request, $output);
            case 'POST':
                return $this->insert($model, $Request, $output);
            default:
                return $output;
        }
    }

    /**
     * Log CRUD operation
     */
    private function logOperation($model, $Request, $type, $errorMessage = null)
    {
        $actionLabels = [
            'PUT'    => 'Update Data',
            'DELETE' => 'Menghapus Data',
            'POST'   => 'Menambahkan Data'
        ];

        // DB::con()->insert('log', [
        //     'tipe'       => $type,
        //     'entitas '   => $model->getTable(),
        //     'aksi'       => $actionLabels[$_SERVER['REQUEST_METHOD']] ?? 'Unknown',
        //     'tambahan'   => $errorMessage ?? json_encode($Request),
        //     'idAnggota ' => $_SESSION['admin']->id ?? null,
        //     'created_at '=> date('Y-m-d H:i:s'),
        // ]);
    }

    /**
     * Update existing record
     */
    public function update($model, $Request, $output)
    {
        $where = EasyStatement::open()->with("{$model->getTable()}.{$model->primary} = ?", $Request->key);
        $model->setStatement($where);
        $model->all();

        $Request->cek = json_decode(json_encode($model->data->first()), true);
        $Request = $this->BeforeUpdate($model, $Request);
        
        $model->upData($Request, [$model->primary => $Request->key]);
        $this->afterUpdate($model, $Request, $Request->key);

        $model->all();
        
        $output['msg'] .= "Data Berhasil Update";
        $output['filter'] = array_values($this->prepareFilter(collect($model->fields), $model->getDB(), $model->getTable()));
        $output['data'] = $model->data->first();

        return $output;
    }

    /**
     * Delete record
     */
    public function hapus($model, $Request, $output)
    {
        $where = EasyStatement::open()->with("{$model->getTable()}.{$model->primary} = ?", $Request->key);
        $model->setStatement($where);
        $model->all();

        $deletedData = $model->data->first();
        $model->delData([$model->primary => $Request->key]);
        $this->afterDelete($deletedData, $model);

        $output['filter'] = array_values($this->prepareFilter(collect($model->fields), $model->getDB(), $model->getTable()));
        $output['msg'] .= "Data berhasil dihapus";

        return $output;
    }

    /**
     * Insert new record
     */
    public function insert($model, $Request, $output)
    {
        $Request = $this->BeforeInsert($model, $Request);
        $id = $model->addData($Request);
        $this->afterInsert($model, $Request, $id);

        $where = EasyStatement::open()->with("{$model->getTable()}.{$model->primary} = ?", $id);
        $model->setStatement($where);
        $model->all();

        $output['msg'] .= "Data Berhasil ditambahkan";
        $output['filter'] = array_values($this->prepareFilter(collect($model->fields), $model->getDB(), $model->getTable()));
        $output['data'] = $model->data->first();

        return $output;
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD Hook Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Hook called after insert
     */
    public function afterInsert($model, $Request, $id) {}

    /**
     * Hook called before insert
     */
    public function BeforeInsert($model, $Request)
    {
        return $Request;
    }

    /**
     * Hook called before update
     */
    public function BeforeUpdate($model, $Request)
    {
        return $Request;
    }

    /**
     * Hook called after update
     */
    public function afterUpdate($model, $Request, $id) {}

    /**
     * Hook called after delete
     */
    public function afterDelete($data, $model) {}
}

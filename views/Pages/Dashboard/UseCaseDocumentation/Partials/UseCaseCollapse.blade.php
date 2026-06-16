@php
    $collapseId = 'usecase-collapse-' . $useCase['no'];
    $stepCount = count($useCase['steps']);
@endphp
<div class="card mb-3 shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
            <div class="font-weight-bold text-primary">Use Case {{ $useCase['no'] }} - {{ $useCase['title'] }}</div>
            <small class="text-muted">{{ $useCase['summary'] }}</small>
        </div>
        <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
            Toggle
        </button>
    </div>
    <div id="{{ $collapseId }}" class="collapse">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="text-muted small">Aktor Utama</div>
                        <div class="font-weight-bold">{{ $useCase['actor'] }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="text-muted small">Jumlah Langkah</div>
                        <div class="font-weight-bold">{{ $stepCount }} langkah</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <div class="text-muted small">Format</div>
                        <div class="font-weight-bold">Aktor dan reaksi sistem</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 47.5%;">Aksi Aktor</th>
                            <th style="width: 47.5%;">Reaksi Sistem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($useCase['steps'] as $index => $step)
                            <tr>
                                <td class="text-center" rowspan="2">{{ $index + 1 }}</td>
                                <td>{{ $step['actor'] }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>{{ $step['system'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-3 col-md-6 mb-3">
    <div class="card stat-card border-left-{{ $variant }} shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-{{ $variant }} text-uppercase mb-1">{{ $title }}</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <span v-text="{{ $valueExpr }}"></span>{{ $suffix ?? '' }}
                    </div>
                    @if(!empty($hint ?? null))
                        <small class="text-muted">{{ $hint }}</small>
                    @endif
                </div>
                <div class="col-auto"><i class="{{ $icon }} fa-2x text-{{ $variant }}"></i></div>
            </div>
        </div>
    </div>
</div>

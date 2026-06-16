<div class="col-12 mb-3" v-if="daftarAnalisa.length > 0">
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history mr-2"></i>Riwayat Analisa
            </h6>
            <div class="text-right">
                <span class="badge badge-info">@{{ daftarAnalisa.length }} analisa</span>
                <small class="text-muted d-block mt-1">Memuat: @{{ selectedPeriodeLabel }}</small>
            </div>
        </div>
        <div class="card-body" style="max-height: 200px; overflow-y: auto;">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-2" v-for="analisa in daftarAnalisa" :key="analisa.id">
                    <div class="card analisa-card border-left-primary h-100"
                        :class="{ active: selectedAnalisaId === analisa.id }"
                        @click="loadHasilAnalisa(analisa.id)">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <small class="text-muted d-block">@{{ analisa.kode_analisa }}</small>
                                <button v-if="canManage"
                                    class="btn btn-sm btn-link text-danger p-0"
                                    @click.stop="hapusAnalisa(analisa.id)"
                                    title="Hapus analisa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <strong class="d-block text-truncate" style="font-size: 0.8rem;">
                                @{{ analisa.nama_analisa }}
                            </strong>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="badge" :class="'badge-' + getStatusClass(analisa.status)">
                                    @{{ analisa.status }}
                                </span>
                                <small class="text-muted">K=@{{ analisa.nilai_k }}</small>
                            </div>
                            <small class="text-info d-block mt-1">
                                Periode: @{{ analisa.periode_nama || (analisa.periode_id || '-') }}
                            </small>
                            <small class="text-muted d-block mt-1">
                                @{{ formatTanggal(analisa.created_at) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

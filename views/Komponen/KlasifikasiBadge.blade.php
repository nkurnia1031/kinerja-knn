@php
    $isVue = !empty($vueExpr ?? null);
    $large = !empty($large ?? false);
    $badgeSizeClass = $large ? ' badge-lg' : '';
    $text = $text ?? '-';
    $classMap = [
        'Sangat Baik' => 'success',
        'Baik' => 'primary',
        'Cukup' => 'warning',
        'Kurang' => 'danger',
        'Sangat Kurang' => 'dark',
    ];
    $badgeClass = $classMap[$text] ?? ($class ?? 'secondary');
@endphp
@if($isVue)
    <span class="badge{{ $badgeSizeClass }}" :class="'badge-' + getKlasifikasiClass({{ $vueExpr }})" v-text="{{ $vueExpr }}"></span>
@else
    <span class="badge{{ $badgeSizeClass }} badge-{{ $badgeClass }}">{{ $text }}</span>
@endif

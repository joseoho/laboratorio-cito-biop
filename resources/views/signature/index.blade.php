@extends('layouts.core')

@section('title', 'Firma digitalizada')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h4 class="mb-0">Firma digitalizada</h4>
            <span class="muted small">Imagen que se agrega a los informes con la casilla «Firma» activada</span>
        </div>
        <a href="{{ route('patients.index') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    @if ($preview)
                        <div class="text-center border rounded p-3 mb-3 bg-white">
                            <img src="{{ $preview }}" alt="Firma actual" style="max-height:120px; max-width:100%;">
                        </div>
                        <p class="small muted mb-3">
                            Firma actual
                            @if ($updatedAt)
                                · actualizada el {{ \Illuminate\Support\Carbon::createFromTimestamp($updatedAt)->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    @else
                        <div class="alert alert-warning">
                            Todavía no hay una firma cargada. Los informes con la casilla «Firma»
                            activada se imprimen sin la imagen.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('signature.store') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label">Subir o reemplazar la firma</label>
                        <input type="file" name="image" accept="image/png,image/jpeg"
                               class="form-control @error('image') is-invalid @enderror" required>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            PNG o JPG, hasta 4 MB. Conviene una imagen con fondo transparente o blanco
                            y recortada, para que se vea bien en el informe.
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-upload me-1"></i>Guardar firma
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body small">
                    <div class="section-title">Cómo se usa</div>
                    <p class="mb-2">
                        En el informe de cada estudio hay una casilla <strong>Firma</strong>.
                        Al activarla y guardar, la impresión y el PDF muestran esta imagen
                        debajo del nombre del patólogo.
                    </p>
                    <p class="mb-2">
                        La firma se guarda en el servidor, no en la computadora de cada usuario.
                    </p>
                    <p class="mb-0 muted">
                        Un informe ya emitido conserva la firma que tenía al emitirse; si se
                        reabre y se vuelve a emitir, toma la firma vigente.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

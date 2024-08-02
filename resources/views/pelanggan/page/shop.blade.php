@extends('pelanggan.layout.index')

@section('content')
    <div class="d-flex flex-row gap-2 mt-4">
        <div class="" style="width: 30%;">
            <div class="card" style="width: 18rem;">
                <div class="card-header">
                    Kategory
                </div>
                <div class="card-body">
                    <div class="accordion accordion-flush" id="accordionFlushExample">

                        @foreach ($label as $item)
                            <div class="d-flex flex-row gap-3">
                                <input type="checkbox" name="kategory" class="kategory" value="{{ $item['type'] }}">
                                <span>{{ $item['type'] }}</span>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-4 mb-5" id="filterResult">
            @if ($data->isEmpty())
                <h1>Belum ada product ...!</h1>
            @else
                @foreach ($data as $p)
                    <div class="card" style="width:200px;">
                        <div class="card-header m-auto">
                            <img src="{{ asset('storage/product/' . $p->foto) }}" alt="baju 1"
                                style="width: 100%;height:130px; object-fit: cover; padding:0;">
                        </div>
                        <div class="card-body">
                            <p class="m-0 text-justify" style="font-size: 14px;"> {{ $p->nama_product }} </p>
                            <p class="m-0"><i class="fa-regular fa-star"></i> 5+</p>
                        </div>
                        <div class="card-footer d-flex flex-row justify-content-between align-items-center">
                            <p class="m-0" style="font-size: 14px; font-weight:600;"><span>IDR
                                </span>{{ number_format($p->harga) }}</p>
                                <form action="{{route('addTocart')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="idProduct" value="{{$p->id}}">
                                    <button type="submit" class="btn btn-outline-primary" style="font-size:24px">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </button>
                                </form>
                        </div>
                    </div>
                @endforeach
        </div>
    </div>
    <div class="pagination d-flex flex-row justify-content-between">
        <div class="showData">
            Data ditampilkan {{ $data->count() }} dari {{ $data->total() }}
        </div>
        <div>
            {{ $data->links() }}
        </div>
    </div>
    @endif

    <script>
        $(document).ready(function() {
            $('.kategory').change(function(e) {
                e.preventDefault();

                const checkboxes = document.querySelectorAll('.kategory');
                let checkedValues = [];

                checkedValues = Array.from(checkboxes)
                    .filter(i => i.checked)
                    .map(i => i.value);

                let checkedValuesString = checkedValues.join(',');

                let encodedCheckedValues = encodeURIComponent(checkedValuesString);

                let url = '/shop?type=' + encodedCheckedValues;

                window.location.href = url;


            });
        });
    </script>
@endsection

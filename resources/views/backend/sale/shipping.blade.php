@extends('backend.layout.main') @section('content')
@if(session()->has('create_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('create_message') !!}</div>
@endif
@if(session()->has('edit_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('edit_message') }}</div>
@endif
@if(session()->has('import_message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('import_message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section class="px-3 py-3">

<!-- Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Location</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      {{Form::open(array('url'=>'location-add','method'=>'post'))}}
<div class="modal-body">
	<div class="row">
	    <div class="col-12">
	        <div class="form-group">
	            {{Form::label('name',__('Name'),array('class'=>'col-form-label')) }}
	            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Name'),'required'=>'required'))}}
	        </div>
	    </div>
	</div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Save')}}</button>
</div>

{{Form::close()}}

    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Shipping</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      {{ Form::open(['url' => 'shipping-post', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
     
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-control-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Name'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('price', __('Price'), ['class' => 'form-control-label']) }}
                {{ Form::text('price', null, ['class' => 'form-control', 'placeholder' => __('Enter Price'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                
                {{ Form::label('Location', __('Location'), ['class' => 'form-control-label']) }}
                <select class="form-control multi-select" name="locations[]" id="note1" multiple>
                    @foreach($locations as $loc)
                    <option value="{{$loc->id}}">{{$loc->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Save') }}</button>
</div>

{{ Form::close() }}

    </div>
  </div>
</div>


<!-- Modal -->
@foreach($shippings as $shipping)
<div class="modal fade" id="editModal2{{$shipping->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Shipping</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      {{Form::model($shipping, array('route' => array('shipping.update', $shipping->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
     
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-control-label']) }}
                {{ Form::text('name', null, ['class' => 'form-control', 'value'=>$shipping->name, 'placeholder' => __('Enter Name'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('price', __('Price'), ['class' => 'form-control-label']) }}
                {{ Form::text('price', null, ['class' => 'form-control', 'value'=>$shipping->price, 'placeholder' => __('Enter Price'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                
                {{ Form::label('Location', __('Location'), ['class' => 'form-control-label']) }}
                <select class="form-control multi-select" name="locations[]" id="note1" multiple>
                    @php
                        $ships_ids=explode(',',$shipping->location_id);
                    @endphp
                    @foreach($locations as $loc)
                    @if(in_array($loc->id,$ships_ids))
                    <option value="{{$loc->id}}" selected>{{$loc->name}}</option>
                    @else
                     <option value="{{$loc->id}}">{{$loc->name}}</option>
                    @endif
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Save') }}</button>
</div>

{{ Form::close() }}

    </div>
  </div>
</div>

@endforeach

   
 <ul class="nav nav-tabs mt-3" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" href="#sale-latest" role="tab" data-toggle="tab">Locations</a>
                  </li>
                  <li class="nav-item mr-3">
                   
                                                           <button type="button" class="btn btn-primary me-2" data-toggle="modal" data-target="#addModal">
  Add Location
</button>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#sale-latest2" role="tab" data-toggle="tab">Shippings</a>
                  </li>
                   
                   <li class="nav-item">
                                                        <button type="button" class="btn btn-primary me-2" data-toggle="modal" data-target="#addModal2">
  Add Shipping
</button>
                  </li>
                
                </ul>

                <div class="tab-content">
                  <div role="tabpanel" class="tab-pane fade show active" id="sale-latest">
                     <div class="table-responsive">
                                    <table class="table mb-0 pc-dt-simple"  id="customer-table" class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Created At') }}</th>
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($locations as $location)
                                                <tr data-name="{{ $location->name }}">
                                                    <td>{{ $location->name }}</td>
                                                    <td>{{ $location->created_at }}</td>
                                                    <td class="Action">

                                                        <div class="d-flex">
                                                           <button type="button" class="btn btn-primary me-2" data-toggle="modal" data-target="#exampleModal{{$location->id}}">
  Edit
</button>
&nbsp;&nbsp;
  
      <a href="{{url('/location-delete/'.$location->id)}}" onclick="return(confirm('Do you want to delete location?'))"><button type="button" class="btn btn-danger mr-2">
 Delete
</button></a>
 


                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                  @foreach ($locations as $location)
                                    <!-- Modal -->
<div class="modal fade" id="exampleModal{{$location->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Location</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
    {{Form::model($location, array('route' => array('location.update', $location->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{Form::label('name',__('Name'))}}
                {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Product Category')))}}
                @error('country_name')
                <span class="invalid-country_name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
</div>
 <div class="modal-footer">
    <button type="button" class="btn  btn-light" data-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
</div>
{{Form::close()}}
      
    </div>
  </div>
</div>
                                  @endforeach
                  </div>
                  
                  <div role="tabpanel" class="tab-pane fade" id="sale-latest2">
                     <div class="table-responsive">
                                    <table class="table mb-0 pc-dt-simple" id="data2">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Price') }}</th>
                                                <th>{{ __('Location') }}</th>
                                                <th>{{ __('Created At') }}</th>
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($shippings as $shipping)
                                                <tr data-name="{{ $shipping->name }}">
                                                    <td>{{ $shipping->name }}</td>
                                                    <td>{{ $shipping->price }}</td>
                                                    <td>
                                                        @php
                                                             $result =   DB::connection('sitesql')->table('locations')->whereIn('id',explode(',',$shipping->location_id))->get()->pluck('name')->toArray();
                                                            $locs=implode(', ',$result);
                                                        @endphp
                                                        {{$locs}}
                                                    </td>
                                                    <td>{{ $shipping->created_at }}</td>
                                                    <td class="Action">

                                                        <div class="d-flex">
                                                           <button type="button" class="btn btn-primary me-2" data-toggle="modal" data-target="#editModal2{{$shipping->id}}">
  Edit
</button>
&nbsp;&nbsp;
  
      <a href="{{url('/shipping-delete/'.$shipping->id)}}" onclick="return(confirm('Do you want to delete location?'))"><button type="button" class="btn btn-danger mr-2">
 Delete
</button></a>
 


                                                        </div>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                  </div>
                  </div>

   
 
</section>





@endsection

@push('scripts')
<script type="text/javascript">
    $("ul#people").siblings('a').attr('aria-expanded','true');
    $("ul#people").addClass("show");
    $("ul#people #customer-list-menu").addClass("active");

    function confirmDelete() {
      if (confirm("Are you sure want to delete?")) {
          return true;
      }
      return false;
    }

    var customer_id = [];

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  $(".deposit").on("click", function() {
        var id = $(this).data('id').toString();
        $("#depositModal input[name='customer_id']").val(id);
  });

  $(".clear-due").on("click", function() {
        var id = $(this).data('id').toString();
        $("#clearDueModal input[name='customer_id']").val(id);
  });

  $(".getDeposit").on("click", function() {
        var id = $(this).data('id').toString();
        $.get('customer/getDeposit/' + id, function(data) {
            $(".deposit-list tbody").remove();
            var newBody = $("<tbody>");
            $.each(data[0], function(index){
                var newRow = $("<tr>");
                var cols = '';

                cols += '<td>' + data[1][index] + '</td>';
                cols += '<td>' + data[2][index] + '</td>';
                if(data[3][index])
                    cols += '<td>' + data[3][index] + '</td>';
                else
                    cols += '<td>N/A</td>';
                cols += '<td>' + data[4][index] + '<br>' + data[5][index] + '</td>';
                cols += '<td><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{trans("file.action")}}<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu"><li><button type="button" class="btn btn-link edit-btn" data-id="' + data[0][index] +'" data-toggle="modal" data-target="#edit-deposit"><i class="dripicons-document-edit"></i> {{trans("file.edit")}}</button></li><li class="divider"></li>{{ Form::open(['route' => 'customer.deleteDeposit', 'method' => 'post'] ) }}<li><input type="hidden" name="id" value="' + data[0][index] + '" /> <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> {{trans("file.delete")}}</button></li>{{ Form::close() }}</ul></div></td>'
                newRow.append(cols);
                newBody.append(newRow);
                $("table.deposit-list").append(newBody);
            });
            $("#view-deposit").modal('show');
        });
  });

  $("table.deposit-list").on("click", ".edit-btn", function(event) {
        var id = $(this).data('id');
        var rowindex = $(this).closest('tr').index();
        var amount = $('table.deposit-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
        var note = $('table.deposit-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(3)').text();
        if(note == 'N/A')
            note = '';

        $('#edit-deposit input[name="deposit_id"]').val(id);
        $('#edit-deposit input[name="amount"]').val(amount);
        $('#edit-deposit textarea[name="note"]').val(note);
        $('#view-deposit').modal('hide');
    });

    var table = $('#customer-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
            },
           
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    } );

  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  if(all_permission.indexOf("customers-delete") == -1)
        $('.buttons-delete').addClass('d-none');
</script>
@endpush

@extends('backend.layout.main') @section('content')
    @if(session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>Edit Ad</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                            {!! Form::open(['route' => 'ad.update', 'method' => 'post', 'files' => true]) !!}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Staff *</strong> </label>
                                        <select required class="form-control selectpicker" id="staff_id" name="staff_id">
                                            @foreach($staffs as $staff)
                                                <option value="{{$staff->id}}" @if($ad->staff_id == $staff->id) selected @endif>{{$staff->name}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('staff_id'))
                                            <span>
                                               <strong>{{ $errors->first('staff_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Social Media Channel *</strong> </label>
                                        <select required class="form-control selectpicker" id="channel" name="channel">
                                            <option value="Facebook" @if($ad->channel == 'Facebook') selected @endif>Facebook</option>
                                            <option value="Twitter" @if($ad->channel == 'Twitter') selected @endif>Twitter</option>
                                            <option value="Instagram" @if($ad->channel == 'Instagram') selected @endif>Instagram</option>
                                            <option value="Threads" @if($ad->channel == 'Threads') selected @endif>Threads</option>
                                            <option value="WhatsApp" @if($ad->channel == 'WhatsApp') selected @endif>WhatsApp</option>
                                            <option value="Youtube" @if($ad->channel == 'Youtube') selected @endif>Youtube</option>
                                            <option value="TikTok" @if($ad->channel == 'TikTok') selected @endif>TikTok</option>
                                        </select>
                                        @if($errors->has('channel'))
                                            <span>
                                               <strong>{{ $errors->first('channel') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Input <span class="asterisk">*</span></label>
                                        <input type="number" name="input" class="form-control" value="{{ $ad->input }}" required>
                                        @if($errors->has('input'))
                                            <span>
                                               <strong>{{ $errors->first('input') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Start Date <span class="asterisk">*</span></label>
                                        <input type="text" name="start" class="form-control date" value="{{ $ad->start }}" required>
                                        @if($errors->has('start'))
                                            <span>
                                               <strong>{{ $errors->first('start') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>End Date <span class="asterisk">*</span></label>
                                        <input type="text" name="end" class="form-control date" value="{{ $ad->end }}" required>
                                        @if($errors->has('end'))
                                            <span>
                                               <strong>{{ $errors->first('end') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="remarks" id="" cols="30" rows="10" class="form-control">{{ $ad->remarks }}</textarea>
                                        @if($errors->has('remarks'))
                                            <span>
                                               <strong>{{ $errors->first('remarks') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="id" value="{{ $ad->id }}">
                            <div class="form-group">
                                <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection

@push('scripts')
    <script type="text/javascript">
        $("ul#people").siblings('a').attr('aria-expanded','true');
        $("ul#people").addClass("show");
        $("ul#people #customer-create-menu").addClass("active");

        $('.asterisk').hide();
        $(".user-input").hide();

        $('input[name="both"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('.asterisk').show();
                $('input[name="company_name"]').prop('required',true);
                $('input[name="email"]').prop('required',true);
            }
            else{
                $('.asterisk').hide();
                $('input[name="company_name"]').prop('required',false);
                $('input[name="email"]').prop('required',false);
            }
        });

        $('input[name="user"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('.user-input').show(300);
                $('input[name="name"]').prop('required',true);
                $('input[name="password"]').prop('required',true);
            }
            else{
                $('.user-input').hide(300);
                $('input[name="name"]').prop('required',false);
                $('input[name="password"]').prop('required',false);
            }
        });
    </script>
@endpush

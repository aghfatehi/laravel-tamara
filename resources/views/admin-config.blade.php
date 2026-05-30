<form class="form-horizontal" action="{{ $updateRoute ?? '#' }}" method="POST">
    @csrf
    <input type="hidden" name="payment_method" value="tamara">

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Sandbox Mode') }}</label>
        </div>
        <div class="col-md-8">
            <label class="switch">
                <input type="checkbox" name="TAMARA_SANDBOX_MODE"
                    @if(config('tamara.sandbox')) checked @endif>
                <span class="slider round"></span>
            </label>
            <span class="text-muted fs-12">{{ __('Enable for testing') }}</span>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Tamara API Token') }}</label>
        </div>
        <div class="col-md-8">
            <input type="text" class="form-control" name="TAMARA_API_TOKEN"
                value="{{ config('tamara.api_token') }}"
                placeholder="{{ __('Enter Tamara API Token') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Country Code') }}</label>
        </div>
        <div class="col-md-8">
            <select class="form-control" name="TAMARA_COUNTRY_CODE">
                @foreach(['SA' => 'Saudi Arabia', 'AE' => 'United Arab Emirates', 'KW' => 'Kuwait', 'BH' => 'Bahrain', 'QA' => 'Qatar', 'OM' => 'Oman'] as $code => $name)
                    <option value="{{ $code }}" @if(config('tamara.country_code') === $code) selected @endif>
                        {{ __($name) }} ({{ $code }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Currency') }}</label>
        </div>
        <div class="col-md-8">
            <select class="form-control" name="TAMARA_CURRENCY">
                @foreach(['SAR' => 'Saudi Riyal', 'AED' => 'UAE Dirham', 'KWD' => 'Kuwaiti Dinar', 'BHD' => 'Bahraini Dinar', 'QAR' => 'Qatari Riyal', 'OMR' => 'Omani Riyal'] as $code => $name)
                    <option value="{{ $code }}" @if(config('tamara.currency') === $code) selected @endif>
                        {{ __($name) }} ({{ $code }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-4">
            <label class="col-form-label">{{ __('Number of Instalments') }}</label>
        </div>
        <div class="col-md-8">
            <select class="form-control" name="TAMARA_INSTALMENTS">
                @foreach([3, 4, 6] as $num)
                    <option value="{{ $num }}" @if((int) config('tamara.instalments') === $num) selected @endif>
                        {{ $num }} {{ __('Instalments') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group mb-0 text-right">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </div>
</form>

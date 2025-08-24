<h2 class="flex justify-between items-center text-xl font-bold mb-6">
    {{ __('Forms Demo') }}
    <code class="text-xs font-normal">resources/views/demo/forms.blade.php</code>
</h2>

<form class="space-y-6">
	{{-- Inputs --}}
	<div class="grid md:grid-cols-2 gap-4">
        <x-inputs.input name="first_name" label="First name" placeholder="Enter your first name" required />
        <x-inputs.input name="last_name" label="Last name" placeholder="Enter your last name" />
    </div>

	{{-- Password --}}
    <div class="grid md:grid-cols-2 gap-4">
        <x-inputs.password name="password" label="Password" :autogenerate="true" placeholder="Enter password" hint="Choose a strong password." required />
        <x-inputs.password name="confirm_password" label="Confirm Password" :autogenerate="true" placeholder="Re-enter password" required />
    </div>

	{{-- Email --}}
    <div class="grid md:grid-cols-2 gap-4">
        <x-inputs.input name="username" type="username" label="Username" placeholder="Enter your username" required />
        <x-inputs.input name="email" type="email" label="Email" placeholder="Enter your email" required />
    </div>

	{{-- File Input --}}
    <div class="flex mb-0">
	    <x-inputs.file-input name="avatar" label="Profile Picture" hint="Upload your avatar." />
    </div>
    <div class="flex">
        <x-media-selector
            name="featured_image"
            label="{{ __('Featured Image') }}"
            :multiple="false"
            allowedTypes="images"
            removeCheckboxName="remove_featured_image"
            removeCheckboxLabel="{{ __('Remove featured image') }}"
            :showPreview="true"
            class="mt-1"
        />
    </div>

	{{-- Select --}}
	<x-inputs.select name="country" label="Country" :options="['bd' => 'Bangladesh', 'us' => 'USA', 'uk' => 'UK']" placeholder="Select country" hint="Choose your country." />

	{{-- Combobox --}}
	<x-inputs.combobox name="city" label="City" :options="['dhaka' => 'Dhaka', 'ny' => 'New York', 'london' => 'London']" placeholder="Select city" hint="Type to search city." />

	{{-- Checkbox --}}
	<x-inputs.checkbox name="terms" label="I agree to terms" hint="You must agree before submitting." />

	{{-- Radio --}}
	<div>
		<span class="form-label">Gender</span>
		<div class="flex gap-4">
			<x-inputs.radio name="gender" value="male" label="Male" />
			<x-inputs.radio name="gender" value="female" label="Female" />
			<x-inputs.radio name="gender" value="other" label="Other" />
		</div>
	</div>

	{{-- Textarea --}}
	<x-inputs.textarea name="bio" label="Bio" placeholder="Tell us about yourself" hint="Max 500 characters." rows="4" />

    {{-- Date Picker --}}
    <x-inputs.date-picker name="birthday" label="Birthday" placeholder="Select date" hint="Pick your birth date" />

	{{-- DateTime Picker --}}
	<x-inputs.datetime-picker name="dob" label="Date of Birth with time" hint="Select your birth date." />

	{{-- Range Input --}}
	<x-inputs.range-input name="experience" label="Experience (years)" min="0" max="30" step="1" hint="How many years of experience?" />

	{{-- Input Group --}}
	<x-inputs.input-group label="Twitter Handle" prepend="@" >
		<x-inputs.input name="twitter" placeholder="yourhandle" />
	</x-inputs.input-group>

	<button type="submit" class="btn btn-primary w-full mt-6">Submit</button>
</form>
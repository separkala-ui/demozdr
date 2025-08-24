<div class="grid md:grid-cols-2 gap-4 mb-2">
    <x-inputs.password
        name="password"
        label="Password"
        :autogenerate="true"
        placeholder="Enter password"
        hint="Choose a strong password."
        required
    />
    <x-inputs.password
        name="confirm_password"
        label="Confirm Password"
        :autogenerate="true"
        placeholder="Re-enter password"
        required
    />
</div>

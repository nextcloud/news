import axios from "@nextcloud/axios";
import { shallowMount, Wrapper } from "@vue/test-utils";
import { store, localVue } from "../setupStore";
import { showError, showSuccess } from "@nextcloud/dialogs";
import { loadState } from "@nextcloud/initial-state";

import AdminSettings from "Components/AdminSettings.vue";

jest.mock("@nextcloud/axios");
jest.mock("@nextcloud/initial-state");
jest.mock("@nextcloud/router");
jest.mock("@nextcloud/dialogs");

describe("AdminSettings.vue", () => {
	"use strict";

	let wrapper: Wrapper<AdminSettings>;

	beforeAll(() => {
		wrapper = shallowMount(AdminSettings, { localVue, store });
	});

	it("should initialize and fetch settings from state", () => {
		expect(loadState).toBeCalledTimes(7);
	});

	it("should send post with updated settings", () => {
		jest.spyOn(axios, "post").mockResolvedValue({ data: {} });

		wrapper.vm.$options?.methods?.update.call(wrapper.vm);

		expect(axios.post).toBeCalled;
	});

	it("should handle bad response", () => {
		console.error = jest.fn();
		wrapper.vm.$options?.methods?.handleResponse.call(wrapper.vm, {
			error: true,
			errorMessage: "FAIL",
		});

		expect(showError).toBeCalled;
	});

	it("should handle success response", () => {
		wrapper.vm.$options?.methods?.handleResponse.call(wrapper.vm, {
			success: "ok",
		});

		expect(showSuccess).toBeCalled;
	});

	afterAll(() => {
		jest.clearAllMocks();
	});
});

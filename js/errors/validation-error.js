import RootError from './root-error.js';

export default class ValidationError extends RootError {
	constructor(result) {
		super('/errors/VALIDATION_ERROR', 'Validation Error', 400, result);
	}
}

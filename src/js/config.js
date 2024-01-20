import fs from 'node:fs';

const server = {
	port: 3000,
	version: getVersion(),
};

function getVersion() {
	return fs.readFileSync('./version.txt', {encoding: 'utf8'}).trim();
}

export {server};

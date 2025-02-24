export default function OnChangeAlert({ getPosts }) {
	if (getPosts.length > 1) {
		return <p className="alert-success">Showing All 10 Posts</p>;
	}
	const postId = getPosts[0].id;
	return <p className="alert-success">Showing Post with ID: {postId}</p>;
}

package service;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import jakarta.ws.rs.DefaultValue;
import jakarta.ws.rs.GET;
import jakarta.ws.rs.Path;
import jakarta.ws.rs.PathParam;
import jakarta.ws.rs.Produces;
import jakarta.ws.rs.client.Client;
import jakarta.ws.rs.client.ClientBuilder;
import jakarta.ws.rs.client.Entity;
import jakarta.ws.rs.core.MediaType;
import jakarta.ws.rs.core.Response;

@Path("/translate")
public class TranslatorResource {

    private final String KEY = "AIzaSyAzcQ3e283hGLlTHzYsMkKzhtnPDhSlO08"; 
    private final String url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" + KEY;

    private String traduit(String text) {
		
    	try {
            Client client = ClientBuilder.newClient();

            String body = "{"
                    + "\"contents\": [{"
                    + "\"parts\": [{"
                    + "\"text\": \"Translate English text into Moroccan dialect (Darija), and only give the text in Darija, do not give any explanation:\\n'" + text + "'\""
                    + "}]"
                    + "}]"
                    + "}";

           
            Response response = client.target(url)
                                          .request(MediaType.APPLICATION_JSON)
                                          .post(Entity.entity(body, MediaType.APPLICATION_JSON));

            if (response.getStatus() != 200) {
                return "sorry,Try again later plaise";
            }

            String translatedText1 = response.readEntity(String.class);
            
            try {
                ObjectMapper objectMapper = new ObjectMapper();

                JsonNode rootNode = objectMapper.readTree(translatedText1);

                JsonNode candidatesNode = rootNode.path("candidates").get(0);
                JsonNode partsNode = candidatesNode.path("content").path("parts").get(0);

                String translatedText = partsNode.path("text").asText();
                return translatedText;
            } catch (Exception e) {
                e.printStackTrace();
            }            
        } catch (Exception e) {
        	 e.printStackTrace();
        }
    	return null;
	}
    
    
    @GET
    @Path("/{text}")
    @Produces(MediaType.TEXT_PLAIN)
    public Response translate(@DefaultValue("vide") @PathParam("text") String text) {

    	if (text.equals("vide") || text.trim().length() == 0) {
            return Response.ok("لاشيء").build();
        }

        String translatedText = traduit(text);
        
        if(translatedText == null) 
        	return Response.status(500)
                    .entity("{\"error\": \"Internal server error"  + "\"}")
                    .build();
        else
        	return Response.ok(translatedText).build();
    }
}
